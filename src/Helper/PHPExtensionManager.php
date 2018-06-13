<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

namespace WPNXM\Webinterface\Helper;

/**
 * Wrapper for handling PHP and ZEND extensions
 * and modifying the php.ini extension section.
 */
class PHPExtensionManager
{
    public $content = '';

    public function readIni()
    {
        if (empty($this->content)) {
            $this->content = file(php_ini_loaded_file());
        }

        return $this->content;
    }

    public function writeIni($content)
    {
        return (bool) file_put_contents(php_ini_loaded_file(), $content);
    }

    public function disable($name)
    {
        if ($name === 'xdebug') {
            if (!$this->uncommentXdebug(false)) {
               $this->addXdebugExtension();
            }
        }

        if ($this->comment($name) === false) {
            $this->addExtension($name, false);
        }
    }

    public function enable($name)
    {
        if ($name === 'xdebug') {
            if (!$this->uncommentXdebug(true)) {
                $this->addXdebugExtension();
            }
        }

        if ($this->uncomment($name) === false) {
            $this->addExtension($name, true);
        }
    }

    public function addExtension($name, $enabled = true)
    {
        #  do not add xdebug as as normal extension
        if ($name === 'xdebug') {
            return $this->addXDebugExtension();
        }

        # prepare line to insert
        $new_line = ($enabled === true) ? '' : ';';
        $new_line .= 'extension=' . $name . "\n";

        # read php.ini and determine position of last extension entry
        $lines = $this->readIni();
        $index = $this->getIndexOfLastPHPExtensionEntry($lines);

        # insert new line after the last extension entry
        array_splice($lines, $index + 1, 0, $new_line);

        # write php.ini
        $content = [];
        $content = implode('', $lines);

        return $this->writeIni($content);
    }

    /**
     * ensure that php.ini contains [Xdebug] section
     *
     * @return bool True, when section for xdebug exists.
     */
    public function checkXdebugSectionExists()
    {
        $lines = $this->readIni();

        foreach ($lines as $index => $line) {
            if(strpos($line, '[XDebug]') !== false) {
                return true; # if found, end.
            }
        }
        return false;
    }

    public function addXdebugExtension()
    {
        // exit early, if section exists already
        if($this->checkXdebugSectionExists() === true) {
            return;
        }

        # xdebug section missing; insert XDebug extension template somewhere near php.ini EOF
        $phpiniEOF = '; Local Variables:';

        # load and prepare xdebug php.ini template
        $tpl_content = file_get_contents(WPNXM_DATA_DIR . '/config-templates/xdebug-section-phpini.tpl');

        $search = [
            '%PHP_EXT_DIR%',
            '%TEMP_DIR%'
        ];

        $replace = [
            WPNXM_DIR . '\bin\php\ext\\',
            WPNXM_DIR . '\temp'
        ];

        $content = str_replace($search, $replace, $tpl_content);

        $new_line =  $content . "\n\n" . $phpiniEOF;

        return $this->replaceLineInPHPINI($phpiniEOF, $new_line);
    }

    public function uncommentXdebug($enabled = true)
    {
        # @todo activating xdebug, means also to disable Zend Optimizer
        # ;zend_extension_manager.optimizer_ts

        # read php.ini
        $lines = [];
        $lines = $this->readIni();

        # prepare line to insert
        $new_line = ($enabled === true) ? '' : ';';
        $new_line .= 'zend_extension="'.WPNXM_DIR.'\php\ext\php_xdebug.dll"' . "\n";

        # prepare line to look for in php.ini
        if ($enabled === true) {
            $old_line = ';zend_extension="'.WPNXM_DIR.'\php\ext\php_xdebug.dll"';
        } else {
            $old_line = 'zend_extension="'.WPNXM_DIR.'\php\ext\php_xdebug.dll"';
        }

        # iterate over php.ini lines
        foreach ($lines as $index => $line) {
            if (strpos($line, $old_line) !== false) {
                $lines[$index] = $new_line; # line replace
            }
        }

        # write php.ini
        $content = [];
        $content = implode('', $lines);

        return $this->writeIni($content);
    }

    private function getIndexOfLastPHPExtensionEntry(array $lines)
    {
        $index_last_extension = 0;
        $last_extension_index = '';

        foreach ($lines as $index => $line) {
            // look for extensions=; but not zend_extension
            if ( (strpos($line, 'extension=') !== false) and (strpos($line, 'zend_extension=') === false)) {
                // lookahead parsing (1 line); look for extensions=; but not zend_extension
                if ( (strpos($lines[$index + 1], 'extension=') !== false) and (strpos($line, 'zend_extension=') === false)) {
                    # not the last element
                    continue;
                } else {
                    # found a possible last "extension=" entry.
                    # is overwritten, till end of $lines.
                    $last_extension_index = $index;
                }
            }
        }

        return $last_extension_index;
    }

    private function comment($name)
    {
        $old_line = $this->getExtensionLineFromPHPINI($name);

        # extension not found, return early
        if ($old_line === null) {
            return false;
        }

        $old_line = trim($old_line);

        # extension found, do comment, if line uncommented
        if (strpos($old_line, 'extension=') !== false) {
            $new_line = ';' . $old_line;
            $this->replaceLineInPHPINI($old_line, $new_line);
        }

        return true;
    }

    private function uncomment($name)
    {
        $old_line = $this->getExtensionLineFromPHPINI($name);

        # extension not found, return early
        if ($old_line === null) {
            return false;
        }

        # extension found, do uncomment, if line commented
        if (strpos($old_line, ';extension=') !== false) {
            $new_line = ltrim($old_line, ';');
            $this->replaceLineInPHPINI($old_line, $new_line);
        }

        return true;
    }

    /**
     * Fetches the line from php.ini where the php extension is found.
     */
    private function getExtensionLineFromPHPINI($name)
    {
        $lines = file(php_ini_loaded_file());
        foreach ($lines as $line) {
            if (strpos($line, $name) !== false) {
                return $line;
            }
        }
    }

    /**
     * @param string $old_line
     * @param string $new_line
     */
    private function replaceLineInPHPINI($old_line, $new_line)
    {
        return $this->writeIni(str_replace($old_line, $new_line, $this->readIni()));
    }

    /**
     * Returns an array with all PHP extensions.
     *
     * $list array has the following structure:
     * key = filename without suffix
     * value = filename with suffix
     *
     * @return array PHP extensions ['php_apc' => 'php_apc.dll']
     */
    public function getAllExtensionFiles()
    {
        static $extensions = [];

        if(!empty($extensions)) {
            return $extensions;
        }

        $files = glob(WPNXM_DIR . '/bin/php/ext/php_*.dll');

        foreach ($files as $key => $file) {
            $value = basename($file);
            $key = str_replace(['php_', '.dll'], '', $value);
            $extensions[ $key ] = $value;
        }

        return $extensions;
    }


    public static function getZendExtensionsWhitelist()
    {
        return array_flip([
            'opcache', // Zend Engine OpCache
            'xdebug',  // XDebug
            'ioncube'  // IonCube Loader
        ]);
    }

    public function getPHPExtensions()
    {
        return array_diff_key($this->getAllExtensionFiles(), self::getZendExtensionsWhitelist());
    }

    public function getZendExtensions()
    {
        return array_intersect_key($this->getAllExtensionFiles(), self::getZendExtensionsWhitelist());
    }

    public function getExtensionsLoaded($onlyZendExtensions = false)
    {
        $extensions = ($onlyZendExtensions === true)
            ? $this->getZendExtensions()
            : $this->getPHPExtensions();

        $list = [];

        foreach ($extensions as $key => $value) {
            if(extension_loaded($key) === true) {
                $list[$key] = true;
            }
        }

        return $list;
    }

    public function getEnabledZendExtensions()
    {
        return $this->getExtensionsLoaded(true);
    }

    public function getEnabledPHPExtensions()
    {
        return $this->getExtensionsLoaded();
    }

    public static function getEnabledPHPExtensionsFromIni()
    {
        $enabled_extensions = [];
        $extension = '';

        // read php.ini
        $ini_file = php_ini_loaded_file();

        $ini = new \Webinterface\Helper\INIReaderWriter($ini_file);
        $ini->read($ini_file);

        $lines = $ini->returnArray();

        // check php.ini array for extension entries
        foreach ($lines as $line) {
            if ($line['type'] !== 'entry') {
                continue;
            }
            if ($line['section'] !== 'PHP' && $line['key'] !== 'extension') {
                continue;
            }
            if(strpos($line['value'], '.dll') !== false) {
                $extension = $line['value'];

                // cut everything of after ".dll"
                // as there might be comments on the line (; #)
                $extension = substr($extension, 0, strpos($extension, '.dll'));

                $enabled_extensions[] = $extension . '.dll';
            }
        }

        asort($enabled_extensions);

        // get rid of the numeric index, by doing a key/value flip
        // this allows to easily check for an extension name by using isset in a foreach loop
        return array_flip($enabled_extensions);
    }

    /**
     * This method ensures that the 'extension_dir' directive is not commented in php.ini.
     * Otherwise PHP will not load any extensions.
     */
    public function checkExtensionDirSet()
    {
        $lines = $this->readIni();

        foreach ($lines as $line) {
            if (strpos($line, '; extension_dir = "ext"') !== false) {
                // uncomment the line
                $new_line = ltrim($line, '; ');
                $this->replaceLineInPHPINI($line, $new_line);
                break;
            }
        }
    }
}

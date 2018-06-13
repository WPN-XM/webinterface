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
 * Wrapper for handling php.ini with ini class.
 */
class PHPINI
{
    /**
     * @return array $ini array structure = 'ini_file', 'ini_array'
     */
    public static function read()
    {
        $ini_file  = php_ini_loaded_file();
        $iniReader = new INIReaderWriter($ini_file);
        $ini_array = $iniReader->returnArray();

        return compact('ini_file', 'ini_array');
    }

    /**
     * @param string $section
     */
    public static function setDirective($section, $directive, $value)
    {
        $ini_file = php_ini_loaded_file();
        self::doBackup($ini_file);
        $ini = new INIReaderWriter($ini_file);
        $ini->set($section, $directive, $value);
        $ini->write($ini_file);

        return true;
    }

    /**
     * @param string $file
     */
    public static function doBackup($file)
    {
        $newFilename = str_replace('.ini', '', $file);
        $newFilename .= '-backup-'.date('dmy-His').'.ini';
        copy($file, $newFilename); // backup current registry
        self::removeOldBackupFiles();

        return true;
    }

    /**
     * keep last 3 files, remove older files.
     */
    public static function removeOldBackupFiles()
    {
        $files = glob(WPNXM_BIN.'php\php-backup-*.ini');
        rsort($files);  
        unset($files[0], $files[1], $files[2]);
        foreach($files as $idx => $file) {
            unlink(trim($file));
        }
    }
}

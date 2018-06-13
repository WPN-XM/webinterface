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

class PHPGracefulRestart
{
    /**
     * Graceful restart of PHP...
     * This starts PHP with "Auto-Disabling of Extensions with Startup Errors".
     *
     * **Steps for Auto-Disabling of Extensions with Errors**
     *
     * 1. user activates "extension" in the webinterface
     *    the extensions has a missing dependency or is out-of-sync with php itself
     * 2. the php.ini change is made
     * 3. the cgi daemon is not stopped!!!
     * ---
     * 4. start PHP in CLI mode in a hidden console (to supress the popup and the output display)
     * 5. grab STDERR output and parse it, to get the extension name
     * 6. write extensions with errors to json file, for later display
     * 7. deactivate the extension(s) with errors in php.ini
     * --
     * 8. restart PHP-cgi daemon (non-gracefully, to activate the good ones)
     * ---
     * 9. present error data from step 6. as ajax response to submit button click
     */
    public static function restart()
    {
        // (4-7)
        self::deactivateExtensionsWithStartupErrors();

        // (8) restart PHP (non-gracefully this time)
        Daemon::restartDaemon('php');
    }

    /**
     * deactivateExtensionsWithStartupErrors
     * see restart, points 4-7.
     */
    public static function deactivateExtensionsWithStartupErrors()
    {
        // (4) start PHP on CLI and get stdout
        exec(WPNXM_BIN."php\php.exe -v", $stdout);

        if (isset($stdout) && is_array($stdout)) {
            // (5) parse output lines and get the extension name
            foreach ($stdout as $idx => $line) {
                if ($line === '') {
                    continue;
                }

                if (strpos($line, 'Unable to load dynamic library') !== false) {
                    /**
                     * $line = "PHP Warning:  PHP Startup: Unable to load dynamic library 'ext\php_odbc.dll'"
                     * $matches[1] = "php_odbc"
                     */
                    if (preg_match("#\'\\w+(.*).dll\'#i", $line, $matches)) {
                        $extension = str_replace('\\', '', $matches[1]);

                        // (6) write extension name to JSON file for informing the user
                        self::logExtensionError($extension);

                        // (7) deactive this extension in php.ini
                        $phpext = new PHPExtensionManager;
                        $phpext->disable($extension);
                    }
                }
            }
        }
    }

    /**
     * @param string $data
     */
    public static function logExtensionError($data)
    {
        $logfile = WPNXM_DIR.'\logs\php_startup_errors.json';

        self::appendJsonFile($logfile, $data);
    }

    /**
     * @param string $filename
     */
    public static function appendJsonFile($filename, $data)
    {
        $handle = fopen($filename, 'a+');

        if (!$handle) {
            throw new \Exception('Error getting file handle: '.$file);
        }

        fseek($handle, 0, SEEK_END); // seek to the end

        if (ftell($handle) > 0) {
            // when we are at the end of is the file empty
            // move back a byte
            // add a trailing comma and the new json string
            fseek($handle, -1, SEEK_END);
            fwrite($handle, ',', 1);
        }

        fwrite($handle, json_encode([$data]));
        fclose($handle);
    }
}

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
 * WPN-XM Server Stack - Helper class for handling evil daemons.
 */
class Daemon
{
    /**
     * @param string $daemon
     */
    public static function isRunning($daemon)
    {
        // shorthands to daemon names; also handle xdebug extension
        switch ($daemon) {
            case 'phpext_xdebug':
            case 'xdebug':
                return extension_loaded('xdebug');
                break;
            case 'phpext_mongo':
                return extension_loaded('mongo');
                break;
            case 'phpext_memcache':
                return extension_loaded('memcache');
                break;
            case 'php':
                $process_name = 'php-cgi.exe';
                break;
            case 'mariadb':
                $process_name = 'mysqld.exe';
            break;
            case 'mongodb':
                $process_name = 'mongod.exe';
                break;
            case 'nginx':
                $process_name = 'nginx.exe';
                break;
            case 'memcached':
                $process_name = 'memcached.exe';
            case 'postgresql':
                $process_name = 'pgsql.exe';
                break;
             default:
                throw new \InvalidArgumentException(
                    sprintf(__METHOD__.'() has no command for the daemon: "%s"', $daemon)
                );
        }

        // lookup daemon executable in process list
        static $output = '';
        if ($output === '') {
            $process = WPNXM_DIR.'bin\tools\process.exe';
            $output  = shell_exec($process);
        }

        if (strpos($output, $process_name) !== false) {
            return true;
        }

        return false;
    }

    public static function startDaemon($daemon, $options = '')
    {
        $hide_console = WPNXM_DIR.'bin\tools\RunHiddenConsole.exe ';

        switch ($daemon) {
            case 'nginx':
                $nginx_folder = WPNXM_DIR.'bin\nginx';
                chdir($nginx_folder); // required for nginx
                self::runCommand("start $hide_console nginx.exe $options");
                break;

            case 'php':
                $folder = WPNXM_DIR.'bin\php';
                chdir($folder); // required for nginx
                self::runCommand("start $hide_console php-cgi.exe -b localhost:9100 $options"); // @todo use php-cgi-spawner
                break;

            case 'spawn-php':
                $folder = WPNXM_DIR.'bin\php';
                chdir($folder); // required for nginx
                self::runCommand("start $hide_console php-cgi.exe -b localhost:9100 $options"); // @todo use php-cgi-spawner
                break;

            case 'mariadb':
                $mysqld_folder = WPNXM_DIR.'bin\mariadb\bin';
                chdir($mysqld_folder); // change to folder
                self::runCommand("start $hide_console mysqld.exe $options");
                break;

            case 'memcached':
                $memcached_daemon = WPNXM_DIR.'bin\memcached\memcached.exe ';
                self::runCommand($hide_console.$memcached_daemon.$options);
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf(__METHOD__.'() has no command for the daemon: "%s"', $daemon)
                );
        }
    }

    public static function stopDaemon($daemon)
    {
        $hide_console = WPNXM_DIR.'bin\tools\RunHiddenConsole.exe ';
        $process_kill = 'taskkill /IM ';

        switch ($daemon) {
            case 'nginx':
                self::runCommand($hide_console.$process_kill.'nginx.exe');
                break;
            case 'mariadb':
                self::runCommand($hide_console.$process_kill.'mysqld.exe');
                break;
            case 'memcached':
                self::runCommand($hide_console.$process_kill.'memcached.exe');
                break;
            case 'php':
                self::runCommand($hide_console.$process_kill.'php-cgi.exe');
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(__METHOD__.'() has no command for the daemon: "%s"', $daemon)
                );
        }
    }

    /**
     * Restarts a daemon by utilizing "restart-wpnxm.bat".
     * Especially the php daemon must (re)-started from the outside.
     *
     * @param  string                    $daemon
     * @throws \InvalidArgumentException
     */
    public static function restartDaemon($daemon)
    {
        chdir(WPNXM_DIR);

        $restart = 'cmd.exe /c restart.bat ';

        switch ($daemon) {
            case 'nginx':
                self::runCommand($restart.'nginx');
                break;
            case 'mariadb':
                self::runCommand($restart.'mariadb');
                break;
            case 'memcached':
                self::runCommand($restart.'memcached');
                break;
            case 'php':
                self::runCommand($restart.'php');
                break;
            case 'graceful-php':
                PHPGracefulRestart::restart();
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(__METHOD__.'() has no command for the daemon: "%s"', $daemon)
                );
        }
    }

    public static function runCommand($cmd)
    {
        pclose(popen($cmd.' 2>nul >nul', 'r'));
    }
}

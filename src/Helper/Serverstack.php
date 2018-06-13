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

class Serverstack
{
    /**
     * Prints the Exclamation Mark Icon.
     * Uses a tooltip (rel="tootip") show the title text.
     *
     * @param  string $image_title_text
     * @return string HTML
     */
    public static function printExclamationMark($image_title_text = '')
    {
        $title = htmlspecialchars($image_title_text);

        return sprintf(
            '<img style="float:right;" src="%sexclamation-red-frame.png" rel="tooltip" alt="%s" title="%s">',
            WPNXM_IMAGES_DIR, $title, $title
        );
    }

    public static function printExclamationMarkLeft($image_title_text = '')
    {
        $title = htmlspecialchars($image_title_text);

        return sprintf(
            '<img src="%sexclamation-red-frame.png" rel="tooltip" alt="%s" title="%s">',
            WPNXM_IMAGES_DIR, $title, $title
        );
    }

    public static function getInstalledComponents()
    {
        // TODO scanner for installed components

        $classes = [];

        $files = glob('*.php');

        foreach ($files as $file) {
            $pi = pathinfo($file);
            if ($pi['filename'] === 'AbstractComponent') {
                continue;
            }
            $classes[] = $pi['filename']; // get rid of extension
        }

        return $classes;
    }

    public static function instantiateInstalledComponents()
    {
        $components = [];

        $classes = self::getInstalledComponents();

        foreach ($classes as $class) {
            $components[] = (new $class);
        }

        return $components;
    }

    /**
     * Get Version - Facade.
     *
     * @param  string                    $componentName
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function getVersion($componentName)
    {
        $fqcn = self::getClassnameForComponent($componentName);

        return (new $fqcn)->getVersion();
    }

    public static function getClassnameForComponent($component)
    {
        // Software\PHPExtension
        if(strpos($component, 'phpext_') !== false || $component === 'xdebug') {
            return '\WPNXM\Webinterface\Software\PHPExtension\\'.$component;
        }

        // Software
        return '\WPNXM\Webinterface\Software\\'.$component;
    }

    /**
     * Tests, if the extension file is found.
     *
     * @param  string $extension Extension name, e.g. xdebug, memcached.
     * @return bool   True if $extension file is found, false otherwise.
     */
    public static function assertExtensionFileFound($extension)
    {
        $files = [
            'apc'       => 'bin\php\ext\php_apc.dll',
            'xdebug'    => 'bin\php\ext\php_xdebug.dll',
            'xhprof'    => 'bin\php\ext\php_xhprof.dll',
            'memcached' => 'bin\php\ext\php_memcache.dll', # file without D
            'zeromq'    => 'bin\php\ext\php_zmq.dll',
            'mongodb'   => 'bin\php\ext\php_mongo.dll',
        ];

        $file = WPNXM_DIR.$files[$extension];

        return is_file($file);
    }

    /**
     * Tests, if an extension is installed.
     * The extension file has to exist and the extensions must be loaded.
     *
     * @param  string $extension Extension to check.
     * @return bool   True if installed, false otherwise.
     */
    public static function isExtensionInstalled($extension)
    {
        if (self::assertExtensionFileFound($extension) === true and extension_loaded($extension)) {
            return true;
        }

        return false;
    }

    /**
     * Attempts to establish a connection to the specified port (on localhost)
     *
     * @param  string                    $daemon Daemon/Service name.
     * @throws \InvalidArgumentException
     * @return bool
     */
    public static function portCheck($daemon)
    {
        $defaulPorts = [
            'nginx'         => '80',
            'mariadb'       => '3306',
            'memcached'     => '11211',
            'php'           => '9000',
            'xdebug'        => '9100',
            'mongodb'       => '27017',
            'mongodb-admin' => '27018',
        ];

        if (isset($defaultPorts[$daemon])) {
            return self::checkPort('127.0.0.1', $defaultPorts[$daemon]);
        }

        throw new \InvalidArgumentException(sprintf('No default port found the daemon: %s', $daemon));
    }

    /**
     * Displays the status of the daemon (running or not) by icon.
     * Shows the daemon status text as tooltip, when hovering.
     *
     * @param  string $daemon Name of the daemon.
     * @return string Embeddable image tag with tooltip.
     */
    public static function getStatusIcon($daemon)
    {            
        // change return text: "extension is loaded" and "daemons is running"
        $runningLabel = (strpos($daemon, 'phpext') !== false) ? 'loaded' : 'running';

        $server = self::getDaemonName($daemon);
        
        if (Daemon::isRunning($daemon) === false) {
            $img   = WPNXM_IMAGES_DIR.'status_stop.png';
            $title = $server .' is not '.$runningLabel.'!';
        } else {
            $img   = WPNXM_IMAGES_DIR.'status_run.png';
            $title = $server .' is '.$runningLabel.'.';
        }

        return sprintf(
            '<img style="float:right;" src="%s" alt="%s" title="%s" rel="tooltip">', $img, $title, $title);
    }

    /**
     * @param string $daemon
     */
    public static function getDaemonName($daemon)
    {
        $daemons = [
            'mariadb'         => 'MariaDB',
            'memcached'       => 'Memcached',
            'mongodb'         => 'MongoDB',
            'nginx'           => 'Nginx',
            'php'             => 'PHP',
            'phpext_memcache' => 'PHP Extension Memcache',
            'phpext_mongo'    => 'PHP Extension Mongo',
            'postgresql'      => 'PostgreSQL',
            'xdebug'          => 'PHP Extension XDebug',
        ];

        if (isset($daemons[$daemon])) {
            return $daemons[$daemon];
        }

        throw new \InvalidArgumentException(sprintf(__METHOD__.'() no name for the daemon: "%s"', $daemon));
    }

    /**
     * Checks, if a component is installed.
     * A component is installed, when all its files exists.
     * See $files array of the component.
     * 
     * @param  string $component
     * @throws \InvalidArgumentException
     * @return bool
     */
    public static function isInstalled($component)
    {
        // these components are always installed. base of the server stack.
        if ($component === 'php' or $component === 'nginx' or $component === 'mariadb') {
            return true;
        }

        $fqcn = self::getClassnameForComponent($component);

        return (new $fqcn)->isInstalled();
    }

    /**
     * Check if there is a service available at a certain port.
     *
     * This function tries to open a connection to the port
     * $port on the machine $host. If the connection can be
     * established, there is a service listening on the port.
     * If the connection fails, there is no service.
     *
     * @param  string $host    Hostname
     * @param  int    $port    Portnumber
     * @param  int    $timeout Timeout for socket connection in seconds (default is 5).
     * @return bool
     */
    public static function checkPort($host, $port, $timeout = 5)
    {
        $socket = fsockopen($host, $port, $errorNumber, $errorString, $timeout);

        if (!$socket) {
            return false;
        }

        fclose($socket);

        return true;
    }

    /**
     * Get name of the service that is listening on a certain port.
     *
     * self::getServiceNameByPort('80')
     *
     * @param  int    $port     Portnumber
     * @param  string $protocol Protocol (Is either tcp or udp. Default is tcp.)
     * @return string Name of the Internet service associated with $service
     */
    public static function getServiceNameByPort($port, $protocol = 'tcp')
    {
        return @getservbyport($port, $protocol);
    }

    /**
     * Get port that a certain service uses.
     *
     * @param  string $service  Name of the service
     * @param  string $protocol Protocol (Is either tcp or udp. Default is tcp.)
     * @return int    Internet port which corresponds to $service
     */
    public static function getPortByServiceName($service, $protocol = 'tcp')
    {
        return @getservbyname($service, $protocol);
    }

    /**
     * Returns the current IP of the user by asking the WPN-XM webserver.
     *
     * @return string the current IP of the user.
     */
    public static function getMyIP()
    {
        $ip = @file_get_contents('http://wpn-xm.org/myip.php');
        if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip) === 1) {
            return $ip;
        }

        return '0.0.0.0';
    }

    /**
     * Get Password - Facade.
     *
     * @param  string                    $component
     * @throws \InvalidArgumentException
     * @return string                    The Password.
     */
    public static function getPassword($component)
    {
        switch ($component) {
            case 'mariadb':
               return (new \WPNXM\Webinterface\Software\MariaDB)->getPassword();
            case 'mongodb':
               return (new \WPNXM\Webinterface\Software\MongoDB)->getPassword();
        }

        throw new \InvalidArgumentException(sprintf('There is no password method for the daemon: %s', $component));
    }

    /**
     * getWindowsVersion()
     *
     * https://en.wikipedia.org/wiki/Windows_NT#Releases
     * https://de.wikipedia.org/wiki/Microsoft_Windows_NT#Versionsgeschichte
     *
     * @return [type] [description]
     */
    public static function getWindowsVersion()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        $regexps = [
            /*'Win 311'       => 'Win16',
            'Win 95'          => '(Windows 95)|(Win95)|(Windows_95)',
            'Win ME'          => '(Windows 98)|(Win 9x 4.90)|(Windows ME)',
            'Win 98'          => '(Windows 98)|(Win98)',
            'WinNT'           => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
            'Win 2000'        => '(Windows NT 5.0)|(Windows 2000)',*/
            'Win XP'          => '(Windows NT 5.1)|(Windows XP)',
            'Win Server 2003' => '(Windows NT 5.2)',
            'Win Vista'       => '(Windows NT 6.0)',
            'Windows 7'       => '(Windows NT 6.1)',
            'Windows 8'       => '(Windows NT 6.2)',
            'Windows 8.1'     => '(Windows NT 6.3)',
            'Windows 10'      => '(Windows NT 6.4)|(Windows NT 10.0)',
        ];

        foreach ($regexps as $name => $pattern) {
            if (preg_match('/'.$pattern.'/i', $useragent)) {
                return $name;
            }
        }

        return 'Unknown ('.$useragent.')';
    }

    /**
     * Returns the Bit-Size as integer.
     *
     * @return int
     */
    public static function getBitSize()
    {
        if (PHP_INT_SIZE === 4) {
            return 32;
        }

        if (PHP_INT_SIZE === 8) {
            return 64;
        }

        return PHP_INT_SIZE; // 16-bit?
    }

    /**
     * Returns Bit-Size as string.
     *
     * @return string 32bit, 64bit
     */
    public static function getBitSizeString()
    {
        return self::getBitSize().'bit';
    }
}

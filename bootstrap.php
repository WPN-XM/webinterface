<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - onwards, Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', -1);
date_default_timezone_set('Europe/Berlin');

// drop request and globals
unset($_REQUEST);
unset($GLOBALS);

/**
 * Definition of Constants
 *
 * Check constants via "index.php?page=debug".
 */
if (!defined('WPNXM_DIR')) {

    // WPNXM Version String
    // The placeholder is replaced during the build of the Installers by the TRAVIS_TAG.
    // The versioning scheme is "major.minor.buildnumber".
    define('WPNXM_STACK_VERSION', '@STACK_VERSION@');

    // Webinterface Version String
    // The placeholder is replaced during the build of the webinterface by the TRAVIS_TAG.
    // The versioning scheme is either "master" or for a tagged release: "major.minor.buildnumber".
    define('WEBINTERFACE_VERSION', '@WEBINTERFACE_VERSION@');

    define('DS', DIRECTORY_SEPARATOR);
    define('NL', "\n");

    // Path Constants -> "c:/.."
    if (defined('PHPUNIT_TESTSUITE_TRAVIS') or (DS === '/')) {
        // Linux Paths
        define('WPNXM_DIR', dirname(__DIR__)); # only the webinterface folder exists on travis
        define('WPNXM_WWW_DIR', WPNXM_DIR.DS); # no www folder
        define('WPNXM_CONTROLLER_DIR', __DIR__.'/src/Controller/');        
        define('WPNXM_HELPER_DIR', __DIR__.'/src/Helper/');
        define('WPNXM_VIEW_DIR', __DIR__.'/src/View/');
        define('WPNXM_DATA_DIR', __DIR__.'/data/');
    } else {
        // Windows Paths
        define('WPNXM_DIR', dirname(dirname(dirname(__DIR__))).DS); // 3 folders up, \www\tools\webinterface
        define('WPNXM_WWW_DIR', WPNXM_DIR.'www'.DS);
        define('WPNXM_CONTROLLER_DIR', __DIR__.'\src\Controller'.DS);
        define('WPNXM_HELPER_DIR', __DIR__.'\src\Helper'.DS);
        define('WPNXM_VIEW_DIR', __DIR__.'\src\View'.DS);
        define('WPNXM_DATA_DIR', __DIR__.'\data'.DS);
    }

    // Web Path Constants -> "http://.."
    $port = ($_SERVER['SERVER_PORT'] !== '80') ? (':'.$_SERVER['SERVER_PORT']) : ''; // add embedded webserver port, in case its running
    define('SERVER_URL', 'http://'.$_SERVER['SERVER_NAME'].$port);
    define('WPNXM_ROOT', SERVER_URL.ltrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '\\').'/');
    define('WPNXM_WWW_ROOT', WPNXM_ROOT.'www/');
    define('WPNXM_WEBINTERFACE_ROOT', '/tools/webinterface/');
    define('WPNXM_ASSETS', '/tools/webinterface/assets/');
    define('WPNXM_IMAGES_DIR', '/tools/webinterface/assets/img/');

    // WPNXM Configuration File
    define('WPNXM_INI',     WPNXM_DIR.'wpn-xm.ini');
    define('WPNXM_BIN',     WPNXM_DIR.'bin'.DS);
    define('WPNXM_TEMP',    WPNXM_DIR.'temp'.DS);

    // Nginx Configurations
    define('WPNXM_NGINX_CONF',                WPNXM_BIN.'nginx\conf\nginx.conf');
    define('WPNXM_NGINX_SITES_ENABLED_DIR',   WPNXM_BIN.'nginx\conf\sites-enabled'.DS);
    define('WPNXM_NGINX_SITES_DISABLED_DIR',  WPNXM_BIN.'nginx\conf\sites-disabled'.DS);

    // Composer managed Vendor folder
    define('VENDOR_DIR', __DIR__.DS.'vendor'.DS);
}

// Register Composer Autoloader
if (!is_file(VENDOR_DIR.'autoload.php')) {
    throw new \RuntimeException(
        'Could not find "vendor/autoload.php".'.PHP_EOL.
        'Did you forget to run "composer install --dev"?'.PHP_EOL
    );
}
require VENDOR_DIR.'autoload.php';

if (!function_exists('showConstants')) {

    /**
     * Returns all user defined constants.
     * Supports several display modes (raw,export, dump).
     *
     * @param string $return The display mode: "raw", "export", "dump" (default).
     * @return
     */
    function showConstants($return = 'dump')
    {
        $array          = get_defined_constants(true);
        $user_constants = $array['user'];

        switch ($return) {
            case 'raw':
                return $user_constants;
            case 'export':
                return var_export($user_constants, true);
            case 'dump':
            default:
                exit('<pre>'.var_dump($user_constants).'</pre>');
        }
    }
}

/**
 * Redirect to Url.
 *
 * @param string $url
 */
function redirect($url)
{
    header('Location: '.$url);
    exit;
}

include __DIR__ . '\src\Core\Autoloader.php';
Autoloader::init();

include __DIR__ . '\src\Core\Errorhandler.php';
Errorhandler::init();

// cache warmup
// create tools menu (cached html menu)
if (!file_exists(WPNXM_DATA_DIR.'tools-topmenu.html')) {
    $projects = new WPNXM\Webinterface\Helper\Projects;
    $projects->listTools();
    unset($projects);
}

$request = new WPNXM\Webinterface\Core\Request();
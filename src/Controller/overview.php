<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

use WPNXM\Webinterface\Software\PHPExtension\XDebug;
use WPNXM\Webinterface\Helper\Serverstack;

function index()
{
    $tpl_data = [
      // load jq, because the database password reset uses jq modal window
      'load_jquery_additionals' => true,
      // version
      'nginx_version'       => Serverstack::getVersion('nginx'),
      'php_version'         => Serverstack::getVersion('php'),
      'mariadb_version'     => Serverstack::getVersion('mariadb'),
      'memcached_version'   => Serverstack::getVersion('memcached'),
      'xdebug_version'      => Serverstack::getVersion('xdebug'),
      'mongodb_version'     => Serverstack::getVersion('mongodb'),
      'postgresql_version'  => Serverstack::getVersion('postgresql'),
      // status
      'nginx_status_icon'            => Serverstack::getStatusIcon('nginx'),
      'php_status_icon'              => Serverstack::getStatusIcon('php'),
      'mariadb_status_icon'          => Serverstack::getStatusIcon('mariadb'),
      'xdebug_status_icon'           => Serverstack::getStatusIcon('xdebug'),
      'mongodb_status_icon'          => Serverstack::getStatusIcon('mongodb'),
      'phpext_mongo_status_icon'     => Serverstack::getStatusIcon('phpext_mongo'),
      'memcached_status_icon'        => Serverstack::getStatusIcon('memcached'),
      'phpext_memcached_status_icon' => Serverstack::getStatusIcon('phpext_memcache'),
      'postgresql_status_icon'       => Serverstack::getStatusIcon('postgresql'),
      // your ip
      'my_ip'                        => Serverstack::getMyIP(),
      // passwords
      'mariadb_password'             => Serverstack::getPassword('mariadb'),
      #'mongodb_password'    => Serverstack::getPassword('mongodb'),
      // which additional components are installed
      'memcached_installed'         => Serverstack::isInstalled('memcached'),
      'xdebug_installed'            => Serverstack::isInstalled('xdebug'),
      'mongodb_installed'           => Serverstack::isInstalled('mongodb'),
      'postgresql_installed'        => Serverstack::isInstalled('postgresql'),
      'phpext_memcached_installed'  => Serverstack::isExtensionInstalled('memcached'),
      'phpext_xdebug_installed'     => Serverstack::isExtensionInstalled('xdebug'),
      'xdebug_extension_type'       => XDebug::getXDebugExtensionType(),
      'xdebug_profiler_active'      => XDebug::isProfilerActive(),
      'server_is_nginx'             => (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) ? true : false,
    ];

    render('page-action', $tpl_data);
}

function stop()
{
    global $request;
    $daemon = $request->get('daemon', null);

    WPNXM\Webinterface\Helper\Daemon::stopDaemon($daemon);
    redirect(WPNXM_WEBINTERFACE_ROOT.'index.php?page=overview');
}

function start()
{
    global $request;
    $daemon = $request->get('daemon', null);

    WPNXM\Webinterface\Helper\Daemon::startDaemon($daemon);
    redirect(WPNXM_WEBINTERFACE_ROOT.'index.php?page=overview');
}

function restart()
{
    global $request;
    $daemon = $request->get('daemon', null);

    WPNXM\Webinterface\Helper\Daemon::restartDaemon($daemon);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // restart - ajax request
    } else {
        // restart - non-ajax restart

      // let windows wait some seconds
      sleep(3);
        redirect(WPNXM_WEBINTERFACE_ROOT.'index.php?page=overview');
    }
}

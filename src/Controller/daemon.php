<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

/**
 * daemon restart
 */
function restart()
{
	global $request;
    $daemon = $request->get('daemon', null);
    
    WPNXM\Webinterface\Helper\Daemon::restartDaemon($daemon);
}

/**
 * daemon start
 */
function start()
{
	global $request;
    $daemon = $request->get('daemon', null);

    WPNXM\Webinterface\Helper\Daemon::startDaemon($daemon);
}

/**
 * daemon stop
 */
function stop()
{
	global $request;
    $daemon = $request->get('daemon', null);

    WPNXM\Webinterface\Helper\Daemon::stopDaemon($daemon);
}

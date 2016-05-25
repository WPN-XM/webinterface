<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016, Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

function index()
{
    global $request;

    /**
     * You need to append the parameter "file" to the URL, e.g. "openfile.php?file=nginx-access-log".
     * Other values include: "nginx-error-log", "php-error-log". See the switch for more.
     */
    $file = $request->get('file', null);

    switch ($file) {
        case 'nginx-access-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\access.log');
        case 'nginx-error-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\error.log');
        case 'php-error-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\php_error.log');
        case 'mariadb-error-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\mariadb_error.log');
        case 'mariadb-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\mariadb.log');
        case 'mongodb-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\mongodb.log');            
        case 'postgresql-log':
            return WPNXM\Webinterface\Helper\OpenFile::openFile(WPNXM_DIR.'\logs\pgsql.log');
        default:
            $msg = sprintf('The method %s() has no case statement for "%s".', __METHOD__, $file);
            throw new InvalidArgumentException($msg);
    }

    header('Location: '.WPNXM_WEBINTERFACE_ROOT.'index.php?page=overview');
}

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

use Webinterface\Helper\Downloader;

class Updater
{
    public static function updateRegistries()
    {
        // WPN-XM Software Registry
        Downloader::downloadIfNotExistsOrOld(
            'https://raw.githubusercontent.com/WPN-XM/registry/master/wpnxm-software-registry.php',
            WPNXM_DATA_DIR.'wpnxm-software-registry.php'
        );
        
        // WPN-XM Software Registry Metadata
        /*Downloader::downloadIfNotExistsOrOld(
            'https://raw.githubusercontent.com/WPN-XM/registry/master/wpnxm-registry-metadata.php',
            WPNXM_DATA_DIR.'wpnxm-registry-metadata.php'
        );*/
           
        // WPN-XM PHP Software Registry
        Downloader::downloadIfNotExistsOrOld(    
            'https://raw.githubusercontent.com/WPN-XM/registry/master/wpnxm-php-software-registry.php',   
            WPNXM_DATA_DIR.'wpnxm-php-software-registry.php'
        );
        
        // PHP Extensions on PECL
        Downloader::downloadIfNotExistsOrOld(
            'https://raw.githubusercontent.com/WPN-XM/registry/master/php-extensions-on-pecl.json',
            WPNXM_DATA_DIR.'php-extensions-on-pecl.json'
        );                    
    }
}

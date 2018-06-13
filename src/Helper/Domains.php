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
 * Domains
 *
 * VirtualHosts are a term and functionality found on Apache Servers.
 * The VirtualHosts concept is unknown to Nginx.
 * For Nginx it's simply a new server directive.
 * In fact, it's simply a "local domain name".
 * That's why WPN-XM calls this Domain.
 */
class Domains
{
    /**
     * Get a list of all domain config files and their loading state.
     *
     * @return array Array of all domain conf files and their loading state.
     */
    public static function listDomains()
    {
        if(!file_exists(WPNXM_NGINX_CONF)) {
            return 'nginx.conf not found';
        }

        if (!self::areEnabledDomainsLoadedByNginxConf()) {
            // tell the world that "nginx.conf" misses to "include sites-enabled/*.conf;"
            exit(sprintf('<div class="error bold" style="font-size: 13px; width: 500px;">
                %s does not include the config files of the sites-enabled folder.<br><br>
                    Please check "nginx.conf" and add "include sites-enabled/*.conf;".</div>',
                WPNXM_NGINX_CONF));
        }

        $enabledDomains  = glob(WPNXM_NGINX_SITES_ENABLED_DIR.'\*.conf');
        $disabledDomains = glob(WPNXM_NGINX_SITES_ENABLED_DIR.'\*.conf');

        $domains = [];

        foreach ($enabledDomains as $idx => $file) {
            $domain           = basename($file, '.conf');
            $domainData       = self::getRootAndServername($file);
            $domains[$domain] = [
                'root'        => $domainData['root'],
                'servernames' => $domainData['servernames'],
                'fullpath'    => $file,
                'filename'    => basename($file),
                'enabled'     => true,
            ];
        }

        foreach ($disabledDomains as $idx => $file) {
            $domain           = basename($file, '.conf');
            $domainData       = self::getRootAndServername($file);
            $domains[$domain] = [
                'root'        => $domainData['root'],
                'servernames' => $domainData['servernames'],
                'fullpath'    => $file,
                'filename'    => basename($file),
                'enabled'     => false,
            ];
        }

        return $domains;
    }

    public static function getRootAndServername($file)
    {
        $content     = file_get_contents($file);
        $root        = self::getRootForDomain($content);
        $servernames = self::getServerNamesForDomain($content);
        return ['root' => $root, 'servernames' => $servernames];
    }

    public static function getRootForDomain($content)
    {
        if(1 === preg_match('#root\s+(www\/.*);#', $content, $matches)) {
            return $matches[1];
        } else {
            return 'root folder not found';
        }
    }

    public static function getServerNamesForDomain($content)
    {
        if(preg_match_all('#server_name\s+(.*);#', $content, $matches)) {
            return $matches[1];
        } else {
            return 'servername not found';
        }
    }

    /**
     * Check, if nginx.conf contains the line to load all enabled domains.
     *
     * @return bool True, if line exists, so domains get loaded. Otherwise, false.
     */
    public static function areEnabledDomainsLoadedByNginxConf()
    {
        $lines = file(WPNXM_NGINX_CONF);
        $matches = [];

        foreach ($lines as $line) {
            // return true, if the line exists and is not commented
            if (preg_match('/(.*)include sites-enabled\/\*/', $line, $matches)) {
                $comment = trim($matches[1]);

                return ($comment === ';' or $comment === '#') ? false : true;
            }
        }

        return false;
    }
}

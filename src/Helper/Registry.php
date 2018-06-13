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

class Registry
{
    /**
     * @return string
     */
    public static function getUrl($component, $version)
    {
        $registry = include WPNXM_DATA_DIR.'wpnxm-software-registry.php';

        if (!isset($registry[$component])) {
            throw new \InvalidArgumentException(sprintf('Component "%s" not found.', $component));
        }

        if (!isset($registry[$component][$version])) {
            throw new \InvalidArgumentException(sprintf('Component "%s" has no version "%s".', $component, $version));
        }

        // PHP Extension - detect constraints
        if (strpos('phpext_', $component) === true) {
            $phpversion = substr(PHP_VERSION, 0, 2);

            return $downloadUrl = $registry[$component][$version][PHP_VERSION];
        }

        return $downloadUrl = $registry[$component][$version];
    }
}

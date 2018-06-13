<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

function index()
{
    $tpl_data = [
        'php_info' => \WPNXM\Webinterface\Helper\PHPInfo::getPHPInfo(),
    ];

    render('page-action', $tpl_data);
}

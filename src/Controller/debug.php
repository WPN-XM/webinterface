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
 * Debug Index
 */
function index()
{
    $tpl_data = [
        'constants' => showConstants('raw'), // showConstants() is defined in bootstrap.php
    ];

    render('page-action', $tpl_data);
}

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
    $component = filter_input(INPUT_GET, 'component', FILTER_SANITIZE_STRING);

    $tpl_data = [
        'no_layout' => true,
        'component' => ucfirst($component),
    ];

    render('page-action', $tpl_data);
}

function update()
{
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (empty($password) === true) {
        echo '<div class="alert alert-danger">No Password given!</div>';

        return;
    }

    $component = filter_input(INPUT_POST, 'component', FILTER_SANITIZE_STRING);

    if (empty($component) === true) {
        echo '<div class="alert alert-danger">No Component given!</div>';

        return;
    }

    switch ($component) {
        case 'mariadb':
            $c = new \WPNXM\Webinterface\Software\MariaDb();
            echo $c->setPassword($password);
            break;
        case 'mongodb':
            $c = new \WPNXM\Webinterface\Software\MongoDb();
            echo $c->setPassword($password);
            break;
        default:
            echo '<div class="alert alert-danger">Component not found.</div>';
    }
}

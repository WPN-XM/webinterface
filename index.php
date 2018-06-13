<?php

/*
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

define('TIME_STARTED', microtime(true));

include 'bootstrap.php';

// page controller
$page           = $request->get('page', 'projects');
$pagecontroller = WPNXM_CONTROLLER_DIR.$page.'.php';
if (is_file($pagecontroller)) {
    include $pagecontroller;
} else {
    throw new \Exception('Error: PageController "'.$page.'" not found ('.$pagecontroller.').');
}

// action controller
$action =  $request->get('action', 'index');
$action = strtr($action, '-', '_'); // minus to underscore conversion
if (!is_callable($action)) {
    throw new \Exception('Error: Action "'.$action.'" not found in PageController "'.$page.'".');
}
$action();

// view renderer (dynamic)
function render($view = 'page-action', $template_vars = [])
{
    // fallback to current page, if called empty
    global $page, $action;
    if ($view === 'page-action') {
        $view = ucfirst($page).DS.$action;
    }
    extract($template_vars);
    ob_start();
    include WPNXM_HELPER_DIR.'Viewhelper.php';
    if (!isset($no_layout) or $no_layout === false) {
        include WPNXM_VIEW_DIR.'header.php';
    }
    $view_file = WPNXM_VIEW_DIR.$view.'.php';
    if (is_file($view_file)) {
        include $view_file;
    } else {
        throw new \Exception('Error: View "'.$view_file.'" not found.'.$view);
    }
    if (!isset($no_layout) or $no_layout === false) {
        include WPNXM_VIEW_DIR.'footer.php';
    }

    return ob_end_flush();
}

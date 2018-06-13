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
    \Webinterface\Helper\Updater::updateRegistries();
        
    $tpl_data = [
        'load_jquery_additionals' => true,
        'registry'                => include WPNXM_DATA_DIR.'wpnxm-software-registry.php',
        'components'              => \WPNXM\Webinterface\Helper\Serverstack::getInstalledComponents(),
        'windows_version'         => \WPNXM\Webinterface\Helper\Serverstack::getWindowsVersion(),
        'bitsize'                 => \WPNXM\Webinterface\Helper\Serverstack::getBitSizeString()
    ];

    render('page-action', $tpl_data);
}

function download()
{
    $component = ($component = filter_input(INPUT_GET, 'component')) ? $component : 'none';
    $version   = ($version = filter_input(INPUT_GET, 'version')) ? $version : '0.0.0';

    if ($component === 'none' or $version === '0.0.0') {
        throw new \InvalidArgumentException('Please specify "component" and "version".');
    }

    $url = \WPNXM\Webinterface\Helper\Registry::getUrl($component, $version);
    
    \WPNXM\Webinterface\Helper\Downloader::download($url);
}

function curl_progress_callback($download_size, $downloaded, $upload_size, $uploaded)
{
    $data = [ 'progress' => [
        'loaded' => $downloaded,
        'total'  => $download_size,
    ]];

    $json = json_encode($data);
    echo '<script>updateProgress('.$json.');</script>'.PHP_EOL;
    ob_flush();
    flush();
}

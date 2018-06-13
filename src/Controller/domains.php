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
        'no_layout' => true, // it's a modal dialog
        'domains'   => \Webinterface\Helper\Domains::listDomains(),
    ];

    render('page-action', $tpl_data);
}

function newDomainFromTemplate($domainName)
{
    $template = file_get_contents(WPNXM_DATA_DIR.'/config-templates/nginx-domain-conf.tpl');
    
    $content = str_replace('%%domain%%', $domainName, $template);
    
    $domainFile = WPNXM_NGINX_DOMAINS_ENABLED_DIR . $domainName.'.conf';
    
    file_put_contents($domainFile, $content);
}

function insert()
{
    $newDomainName = $request->get('newdomain', null);    
      
    newDomainFromTemplate($newDomainName);
    
    // check for "COM" (php_com_dotnet.dll)
    if (!class_exists('COM') and !extension_loaded('com_dotnet')) {
        $msg = 'COM class not found. Enable the extension by adding "extension=php_com_dotnet.dll" to your php.ini.';
        throw new Exception($msg);
    }
    $WshShell = new COM('WScript.Shell');

    // reload nginx configuration
    $cmdRestartNginx = 'cmd /c "'.WPNXM_DIR.'\bin\nginx\nginx.exe -p '.WPNXM_DIR.' -c '.WPNXM_DIR.'\bin\nginx\conf\nginx.conf -s reload"';
    $oExec           = $WshShell->run($cmdRestartNginx, 0, false);

    // add the new virtual host to the windows .hosts file using the "hosts" tool
    $cmdAddHosts = 'cmd /c "'.WPNXM_DIR.'\bin\tools\hosts'.' add '.$_SERVER['SERVER_ADDR'].' '.$newDomainName.' # added by WPN-XM"';
    passthru($cmdAddHosts);

    // flush ipcache
    $cmdIpflush = 'ipconfig /flushdns';
    $oExec      = $WshShell->run($cmdIpflush, 0, false);

    // wait a second for dns flush
    sleep(1);

    // forward to new host
    header("Location: http://$newDomainName/");
}

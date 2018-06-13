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
 * config index
 */
function index()
{
    $serverstack = new \WPNXM\Webinterface\Helper\Serverstack;
    
    $tpl_data = [
        'load_jquery_additionals' => true,
        'mongodb_installed'       => $serverstack->isInstalled('mongodb'),
        'xdebug_installed'        => $serverstack->isInstalled('xdebug'),
    ];

    render('page-action', $tpl_data);
}

function project()
{
    $project = filter_input(INPUT_GET, 'project');

    echo $project;
}

/**
 * showTab is the Tab Controller - handling GET requests for tab pages.
 * Ajax calls to tab pages use URLs like: "index.php?page=config&action=showtab&tab=xy".
 * Each tab function renders content for inline display in the tab-content container.
 */
function showtab()
{
    $tab       = filter_input(INPUT_GET, 'tab');
    $tab       = strtr($tab, '-', '_');
    $tabAction = 'tab_'.$tab;
    if (false === is_callable($tabAction)) {
        throw new \Exception(sprintf('The controller method "%s" for the Tab "%s" was not found!', $tabAction, $tab));
    }
    $tabAction();
}

function tab_help()
{
    render('Config\tab-help', ['no_layout' => true]);
}

function tab_mariadb()
{
    render('Config\tab-mariadb', ['no_layout' => true]);
}

function tab_mongodb()
{
    render('Config\tab-mongodb', ['no_layout' => true]);
}

function tab_nginx()
{
    $tpl_data = [
        'no_layout'                => true,
        'nginx_access_toggle_form' => renderNginxAccessToggleFrom(),
    ];

    render('Config\tab-nginx', $tpl_data);
}

function tab_nginx_domains()
{
    $projects = new \WPNXM\Webinterface\Helper\Projects;
    $domains  = new \WPNXM\Webinterface\Helper\Domains;

    $tpl_data = [
        'no_layout'       => true,
        'project_folders' => $projects->getProjects(true),
        'domains'         => $domains->listDomains(),
    ];

    render('Config\tab-nginx-domains', $tpl_data);
}

function tab_php()
{
    $tpl_data = [
        'no_layout'                 => true,
        'php_version_switcher_form' => renderPhpVersionSwitcherForm(),
        'ini'                       => \WPNXM\Webinterface\Helper\PHPINI::read(),
    ];

    render('Config\tab-php', $tpl_data);
}

function tab_php_ext()
{
    $extensionManager = new \WPNXM\Webinterface\Helper\PHPExtensionManager();
    
    $pickle = new \WPNXM\Webinterface\Software\Pickle;
    $pickle_installed = $pickle->isInstalled();
        
    $tpl_data = [
        'no_layout'                        => true,
        'pickle_installed'                 => $pickle_installed,
        'pickle_version'                   => ($pickle_installed === true ? $pickle->getVersion() : '---'), 
        'number_available_zend_extensions' => count($extensionManager->getZendExtensions()),
        'number_enabled_zend_extensions'   => count($extensionManager->getEnabledZendExtensions()),
        'number_available_php_extensions'  => count($extensionManager->getPHPExtensions()),
        'number_enabled_php_extensions'    => count($extensionManager->getEnabledPHPExtensions()),
        'php_extensions_form'              => renderPHPExtensionsFormContent(),
        'zend_extensions_form'             => renderZendExtensionsFormContent(),
    ];

    render('Config\tab-phpext', $tpl_data);
}

function getNumberOfExtensionsLoaded()
{
    $extensionManager = new \WPNXM\Webinterface\Helper\PHPExtensionManager();

    $tpl_data = [
        'number_available_zend_extensions' => count($extensionManager->getZendExtensions()),
        'number_enabled_zend_extensions'   => count($extensionManager->getEnabledZendExtensions()),
        'number_available_php_extensions'  => count($extensionManager->getPHPExtensions()),
        'number_enabled_php_extensions'    => count($extensionManager->getEnabledPHPExtensions()),
    ];

    echo json_encode($tpl_data);
}

function tab_xdebug()
{
    $xdebug_installed = \WPNXM\Webinterface\Helper\Serverstack::isInstalled('xdebug');

    $tpl_data = [
        'no_layout'        => true,
        'xdebug_installed' => $xdebug_installed,
        'ini_settings'     => ($xdebug_installed) ? ini_get_all('xdebug') : [],
    ];

    render('Config\tab-xdebug', $tpl_data);
}

function update_php_extensions()
{
    /* extensions to enable */
    $extensions = filter_input(INPUT_POST, 'extensions', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

    $extensionManager       = new \WPNXM\Webinterface\Helper\PHPExtensionManager();
    $enabledExtensions      = array_flip($extensionManager->getEnabledPHPExtensionsFromIni());
    var_dump($enabledExtensions);
    $disableTheseExtensions = array_values(array_diff($enabledExtensions, $extensions)); // diff + re-index

    foreach ($extensions as $extension) {
        $extensionManager->enable($extension);
    }

    foreach ($disableTheseExtensions as $extension) {
        $extensionManager->disable($extension);
    }

    // prepare response data
    $responseData = [
        'enabled_extensions'  => $extensions,
        'disabled_extensions' => $disableTheseExtensions,
        'responseText'        => 'Extensions updated. Restarting PHP ...',
    ];

    // send as JSON
    echo json_encode($responseData);

    // Note: restart of PHP is done via AJAX
}

function update_zend_extensions()
{
    /* zend extensions to enable */
    $extensions = filter_input(INPUT_POST, 'extensions', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

    $extensionManager       = new \WPNXM\Webinterface\Helper\PHPExtensionManager();
    $availableExtensions    = $extensionManager->getZendExtensions();
    $enabledExtensions_lo   = $extensionManager->getEnabledZendExtensions();
    $enabledExtensions      = array_flip($extensionManager->getEnabledPHPExtensionsFromIni());
    var_dump($extensions, $availableExtensions, $enabledExtensions, $enabledExtensions_lo);

    $disableTheseExtensions = array_diff($enabledExtensions, $extensions);
    $disableTheseExtensions = array_values($disableTheseExtensions); // re-index

    var_dump('Enable', $extensions); /* show extensions to enable */

    foreach ($extensions as $extension) {
        $extensionManager->enable($extension);
    }

    var_dump('Disable', $disableTheseExtensions); /* show extensions to disable */

    foreach ($disableTheseExtensions as $extension) {
        $extensionManager->disable($extension);
    }

    // prepare response data
    $tpl_data = [
        'enabled_extensions'  => $extensions,
        'disabled_extensions' => $disableTheseExtensions,
        'responseText'        => 'Extensions updated. Restarting PHP ...',
    ];

    // send as JSON
    echo json_encode($array);

    // restarting of PHP is run via AJAX
}

function renderNginxAccessToggleFrom()
{
    // $("input[name=nginx_access_toggle]:checked").val()

    $nginxConfig             = new \WPNXM\Webinterface\Software\Nginx\NginxConfig;
    $allow_only_local_access = $nginxConfig->isAllowedOnlyLocalAccess();

    // form
    $html = '<form action="index.php?page=config&action=update_nginx_access_state" method="POST">';

    $html .= '<fieldset class="btn-group" data-toggle="buttons">';
    $html .= '<p><b>Nginx Access Toggle</b></p>';

    // radiobutton "allow only local access"
    $html .= '<label class="btn ';
    $html .= ($allow_only_local_access === true) ? 'btn-success active' : 'btn-default';
    $html .= '">';
    $html .= '<input type="radio" name="nginx_access_toggle" value="allow_only_local_access" ';
    $html .= ($allow_only_local_access === true) ? 'checked="checked" ' : '';
    $html .= '>allow only local access</label>';

    // radiobutton "allow_access_from_any-computer"
    $html .= '<label class="btn ';
    $html .= ($allow_only_local_access === false) ? 'btn-success active' : 'btn-default';
    $html .= '">';
    $html .= '<input type="radio" name="nginx_access_toggle" value="allow_access_from_any_computer" ';
    $html .= ($allow_only_local_access === false) ? 'checked="checked" ' : '';
    $html .= '>allow access from any computer</label>';

    $html .= '</fieldset>';

    // form buttons
    $html .= '<div class="right">
                <button type="reset" class="btn btn-danger"><i class="icon-remove"></i> Reset</button>
                <button type="submit" class="btn btn-success"><i class="icon-ok"></i> Submit</button>
              </div>';

    $html .= '</form>';

    return $html;
}

function update_nginx_access_state()
{
    $toggle_state = filter_input(INPUT_POST, 'nginx_access_toggle');

    if(!isset($toggle_state)) {
        throw new \Exception('The "nginx_access_toggle" value is missing!');
    }

    $nginxConfig = new \WPNXM\Webinterface\Software\Nginx\NginxConfig;

    if ($toggle_state === 'allow_access_from_any_computer') {
        $nginxConfig->allowAccessFromAnyComputer();        
    } 
    elseif ($toggle_state === 'allow_only_local_access') {
        $nginxConfig->allowOnlyLocalAccess();        
    } 

    echo '<div class="modal"><p class="info">Saved. Nginx Access...</div>';    
}

function renderPHPExtensionsFormContent()
{
    return renderExtensionsFormContent();
}

function renderZendExtensionsFormContent()
{
    return renderExtensionsFormContent(true);
}

function renderExtensionsFormContent($zendExtensions = false)
{
    global $request;

    $extensionManager = new \WPNXM\Webinterface\Helper\PHPExtensionManager();

    if ($zendExtensions === true) {
        $available_extensions = $extensionManager->getZendExtensions();
        $enabled_extensions   = $extensionManager->getEnabledZendExtensions();
    } else {
        $available_extensions = $extensionManager->getPHPExtensions();
        $enabled_extensions   = $extensionManager->getEnabledPHPExtensions();
    }

    $html_checkboxes = '';
    $i               = 1; // start at first element
    $itemsTotal      = count($available_extensions); // elements total
    $itemsPerColumn  = ceil($itemsTotal / 4);

    // use list of available_extensions to draw checkboxes
    foreach ($available_extensions as $name => $file) {

        // if extension is enabled, check the checkbox
        $checked = (isset($enabled_extensions[$name]) && $enabled_extensions[$name]) ? true : false;

        // render column opener
        if ($i === 1) {
            $html_checkboxes .= '<div class="form-group">';
        }

        // the input tag is wrapped by the label tag
        $html_checkboxes .= '<label class="checkbox';
        $html_checkboxes .= ($checked === true) ? ' active-element">' : ' not-active-element">';
        $html_checkboxes .= '<input type="checkbox" name="extensions[]" value="'.$file.'" ';
        $html_checkboxes .= ($checked === true) ? 'checked="checked" ' : '';
        $html_checkboxes .= '>';
        $html_checkboxes .= $name;
        $html_checkboxes .= '</label>';

        if ($i == $itemsPerColumn or $itemsTotal === 1) {
            $html_checkboxes .= '</div>';
            $i = 0; /* reset column counter */
        }

        $i++;
        $itemsTotal--;
    }
    
    $tab = $request->get('tab');
    
    if ($request->isAjax() && !isset($tab)) {
        echo $html_checkboxes;
    } else {
        return $html_checkboxes;
    }
}

function update_phpini_setting()
{
    $directive = filter_input(INPUT_POST, 'directive');
    $value     = filter_input(INPUT_POST, 'value');

    // @todo figure out, if we need to set a ini [section], in order to save the directive correctly?
    // @see IniReaderWriter::set() $section is not used there
    $section = '';

    WPNXM\Webinterface\Helper\PHPINI::setDirective($section, $directive, $value);
    WPNXM\Webinterface\Helper\Daemon::restartDaemon('php');

    echo '<div class="modal"><p class="info">Saved. PHP restarted.</div>';
}

function update_phpversionswitch()
{
    $new_version = filter_input(INPUT_POST, 'new_php_version');

    WPNXM\Webinterface\Helper\Daemon::stopDaemon('php');

    WPNXM\Webinterface\Helper\PHPVersionSwitch::switchVersion($new_version);

    WPNXM\Webinterface\Helper\Daemon::restartDaemon('php');

    echo '<div class="modal"><p class="info">PHP version switched. PHP restarted.</div>';
}

function renderPhpVersionSwitcherForm()
{
    $versionFolders      = WPNXM\Webinterface\Helper\PHPVersionSwitch::getVersions();
    $currentVersion      = WPNXM\Webinterface\Helper\PHPVersionSwitch::getCurrentVersion();
    $number_php_versions = count($versionFolders);

    $options = '';
    foreach ($versionFolders as $folder) {
        $options .= '<option value="'.$folder['php-version'].'"';
        $options .= ($folder['php-version'] === $currentVersion) ? ' selected' : '';
        $options .= '>'.$folder['php-version'].'</option>';
    }

    $html = '<div class="col-md-6">';
    $html .= '<form id="php-version-switcher-form" action="index.php?page=config&action=update_phpversionswitch" method="POST">';

    $html .= '<div class="form-group col-md-6">';
    $html .= '<select class="form-control" name="new-php-version" size="'.$number_php_versions.'">';
    $html .= $options;
    $html .= '</select>';
    $html .= '</div>';

    // show switch button, if multiple PHP version present
    if ($number_php_versions > 1) {
        $html .= '<div class="pull-right"></br>';
        $html .= '<button class="btn btn-success" type="submit"><i class="icon-ok"></i> Switch</button>';
        $html .= '&nbsp;';
        $html .= '<button class="btn btn-danger" type="reset"><i class="icon-remove"></i> Reset</button>&nbsp;';
        $html .= '</div>';
    }

    $html .= '</form>';
    $html .= '</div>';
    $html .= '<div class="clearfix"></div>';

    return $html;
}

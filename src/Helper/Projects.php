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

class Projects
{
    private $projectFolders = [];
    private $domains        = [];

    /**
     * The "toolDirectories" array contains paths of the "/www/tools" folder.
     * These paths are administration tools for WPN-XM, shipped with the distribution.
     * Some of these tools do not provide an "index.php" at the top-level.
     * This array keeps the relation between a "tool folder" and it's "startup script"
     * or "startup folder with index.php".
     *
     * @var array
     */
    private $toolDirectories = [
        'adminer'           => 'adminer/adminer.php',
        'memadmin'          => '',
        'phpmemcachedadmin' => '',
        'phpmyadmin'        => '',
        'rockmongo'         => '',
        'uprofiler'         => 'uprofiler/uprofiler_html',
        'webgrind'          => '',
        'wincache'          => '',
        'xcache'            => '',
        'xhprof'            => 'xhprof/xhprof_html',
    ];

    public function __construct()
    {
        $this->projectFolders = $this->getProjects();
        $this->domains        = \WPNXM\Webinterface\Helper\Domains::listDomains();   
    }

    /**
     * Returns all project folders from "/www", excluding the "tools" folder.
     *
     * @return array
     */
    public function getProjects()
    {
        $dirs   = [];
        $handle = opendir(WPNXM_WWW_DIR);

        while ($dir = readdir($handle)) {
            // exclude dot folders and tools folder
            if ($dir === '.' or $dir === '..' or $dir === 'tools') {
                continue;
            }

            if (is_dir(WPNXM_WWW_DIR.$dir)) {
                $dirs[] = $dir;
            }
        }

        closedir($handle);
        asort($dirs);

        return $dirs;
    }

    public function listProjects()
    {
        if ($this->getNumberOfProjects() == 0) {
            return 'No project dirs found.';
        }

        $html = '<table width="100%">';
        foreach ($this->projectFolders as $dir) {
            $html .= '<tr height="35px">';                       
            // 1. display the folder name of a project and link to its realpath            
            $html .= '<td class="pull left"><a class="folder" href="'.WPNXM_ROOT.$dir.'">'.self::shortenString($dir).'</a></td>';            
            // 2. display the domains
            $html .= '<td><font style="font-size: 12px">'.$this->renderDomains($dir).'</font></td>';            
            // 3. display the repository links (home, github) and settings link
            $html .= ' <td class="right" width="120px">';           
            // TODO $html .= $this->renderSettingsLink($dir);
            $html .= $this->renderRepositoryLinks($dir);
            $html .= '</td></tr>';
        }

        return $html.'</table>';
    }
    
    private static function shortenString($string, $maxLength = 22)
    {
        return (strlen($string) > $maxLength) ? substr($string,0,$maxLength).'...' : $string;
    }
       
    public function renderDomains($dir)
    {         
        if(isset($this->domains[$dir]))
        {
            $domain = $this->domains[$dir];            
        
            $html = '';
            foreach($domain['servernames'] as $server_name) {
                $html .= '<div class="left"><i class="angle right icon"></i>'
                    . '<a href="http:\\\\' . $server_name . '">' . $server_name . '</a>'
                    . '<br></div>';
            }
            
            return $html;
        }
    }

    public function listTools()
    {
        $this->checkWhichToolsAreInstalled();

        $html = '';

        foreach ($this->toolDirectories as $dir => $href) {
            $link = ($href === '') ? (WPNXM_ROOT.'tools/'.$dir) : (WPNXM_ROOT.'tools/'.$href);

            $html .= '<li class="list-group-item">';
            $html .= '<a class="folder" href="'.$link.'">'.$dir.'</a>';
            $html .= '</li>';
        }

        // write the html list to file. this acts as a cache for the tools topmenu.
        // the file is rewritten each time "Tools & Projects" is opened,
        // because the user might have deleted or installed a new tool.
        file_put_contents(WPNXM_DATA_DIR.'tools-topmenu.html', $html);

        return '<ul class="list-group text-left">'.$html.'</ul>';
    }

    /**
     * Returns links with icons to Github, Travis-CI and Packagist.
     */
    public function renderRepositoryLinks($dir)
    {
        $html = '<div class="btn-group pull-right" style="margin-right: 10px; margin-top: -3px;">';

        /**
         * Home - Link to project website
         *
         * If the project folder contains a "composer.json" file
         * display a home link using the homepage attribute
         */
        if ($this->hasComposerConfig($dir)) {
            $composer = json_decode(file_get_contents(WPNXM_WWW_DIR.$dir.'/composer.json'), true);

            if (isset($composer['homepage'])) {
                $html .= '<a class="btn btn-default btn-xs" href="'.$composer['homepage'].'">';
                $html .= '<i class="large home icon"></i>';
                $html .= '</a>';
            }
        }

        /**
         * Github - Link to project on Github
         *
         * If the project folder contains a ".git/config" file
         * with a github repo link, display a "github.com" link.
         */
        if ($this->isGitRepoAndHostedOnGithub($dir)) {
            if (is_array($composer) && isset($composer['name'])) {
                $githubLink = 'https://github.com/'.$composer['name'];
            } else {
                $githubLink = 'https://github.com/'.$this->getProjectNameFromGitConfig($dir);
            }

            $html .= '<a class="btn btn-default btn-xs" href="'.$githubLink.'">';
            $html .= '<img src="'.WPNXM_IMAGES_DIR.'github-mark.png" style="height: 18px; width: 17px;" />';
            $html .= '</a>';
        }

        return $html.'</div>';
    }

    public function readProjectGitConfig($dir)
    {
        return file_get_contents(WPNXM_WWW_DIR.$dir.'/.git/config');
    }

    public function getProjectNameFromGitConfig($dir)
    {
        $gitConfig = $this->readProjectGitConfig($dir);
        preg_match('#github.com(?:\:|/)(.*).git#i', $gitConfig, $matches);

        return $matches[1];
    }

    public function isGitRepoAndHostedOnGithub($dir)
    {
        if ($this->hasGitConfig($dir) === true) {
            $gitConfig = $this->readProjectGitConfig($dir);
            if (false !== strpos($gitConfig, 'github')) {
                return true;
            }
        }

        return false;
    }

    public function getPackagistPackageDescription($package = '')
    {
        $url = sprintf('https://packagist.org/packages/%s.json', $package);

        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
            ],
        ]);

        // silenced: because this throws a warning, if offline
        $json = @file_get_contents($url, false, $context);

        $array = json_decode($json, true);

        if (isset($array['status']) && $array['status'] === 'error') {
            \WPNXM\Webinterface\Helper\Serverstack::printExclamationMark(
                'The request to packagist.org failed. This might be a service problem.'.
                ' Please ensure that HTTPS streamwrapper support is enabled in php.ini (extension=php_openssl.dll).'
            );
        }

        return $array;
    }

    /**
     * Returns the correct "package name" for building a Travis-CI or Github URL
     * This returns [package][repository] instead of [name], which is lowercased.
     */
    public function getPackageName(array $packageDescription = [])
    {
        return str_replace('https://github.com/', '', $packageDescription['package']['repository']);
    }

    public function renderSettingsLink($dir)
    {
        $html = '';

        // display "settings" cog wheel for this project. Modal shows a configuration screen "per project".
        $html .= '<a class="btn-new-domain floatright" data-toggle="modal" data-target="#myModal" ';
        $html .= ' href="'.WPNXM_WEBINTERFACE_ROOT.'index.php?page=projects&action=edit&project='.$dir.'">';
        $html .= '<i class="glyphicon glyphicon-cog"></i></a>';

        /*if (false === $this->isDomain($dir)) {
            // display link to add a new domain for this directory
            $html .= '<a class="btn-new-domain floatright" ';
            $html .= ' href="' . WPNXM_WEBINTERFACE_ROOT . 'index.php?page=domains&newdomain=' . $dir . '">';
            $html .= 'New Domain</a>';
        } else {
            // display link to the domain
            $html .= '<a class="floatright" href="http://' . $dir . '/">' . $dir . '</a>';
        }*/

        return $html;
    }

    /**
     * Check, if a seperate domain exists in \bin\nginx\conf\domains\
     */
    public function isDomain($dir)
    {
        return is_file(WPNXM_DIR.'/bin/nginx/conf/domains/'.$dir.'.conf');
    }

    public function hasTravisConfig($dir)
    {
        return is_file(WPNXM_WWW_DIR.$dir.'/.travis.yml');
    }

    public function hasComposerConfig($dir)
    {
        return is_file(WPNXM_WWW_DIR.$dir.'/composer.json');
    }

    public function hasGitConfig($dir)
    {
        return is_file(WPNXM_WWW_DIR.$dir.'/.git/config');
    }

    /**
     * tools directories are hardcoded.
     * because we don't know which ones the user installed, we check for existence.
     * if a tool dir is not there, remove it from the list.
     */
    public function checkWhichToolsAreInstalled()
    {
        foreach ($this->toolDirectories as $dir => $href) {
            if (is_dir(WPNXM_WWW_DIR.'tools\\'.$dir) === false) {
                unset($this->toolDirectories[$dir]);
            }
        }
    }

    public function getNumberOfProjects()
    {
        return count($this->projectFolders);
    }

    public function getNumberOfTools()
    {
        $this->checkWhichToolsAreInstalled();

        return count($this->toolDirectories);
    }
}

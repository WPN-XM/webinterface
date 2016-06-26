<?php
/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2016, Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

namespace WPNXM\Webinterface\Software;

/**
 * WPN-XM Webinterface - Abstract Base Class for Software Components.
 *
 * The class provides basic methods to gather pieces of information
 * about a software component.
 */
abstract class SoftwareBase
{
    /* @var string Printable name of the component. */
    public $name;

    /* @var string Name of the component in the registry. */
    public $registryName;

    /* @var string Type (PHP Extensions, Daemon). */
    public $type;

    /**
     * @var array Array with all essential files of the component.
     *            For making sure, that the component is installed.
     */
    public $files = [];

    /* @var string The configuration file of the component, if any. */
    public $configFile = '';

    /* @var string The target folder (or base folder) of the installation. */
    public $installationFolder = '';

    /**
     * Array with some essential file dependencies of the component.
     * For making sure, that the component's dependencies are also installed.
     */
    public $dependencyFiles = [];

    public $downloadURL = '';

    /**
     * Checks, if a component is installed.
     * A component is installed, if all its files exist.
     * The files are defined in the $files array.
     *
     * @return bool True, if installed, false otherwise.
     */
    public function isInstalled()
    {
        $bool  = false;
        $files = (array) $this->files; 
        
        foreach ($files as $file) {
            $bool = file_exists(WPNXM_DIR.$file);            
        }

        return $bool;
    }

    public function checkDependencies()
    {
        $bool = false;
        foreach ($this->dependencyFiles as $file) {
            $bool = $bool && file_exists($file);
        }

        return $bool;
    }

    public function hasDependencies()
    {
        return (empty($dependencyFiles) === true) ? false : true;
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }

    public function getInstallationFolder()
    {
        return $this->installationFolder;
    }

    public function download($url = '', $targetFolder = '')
    {
        $url          = ($url === '') ? $this->downloadURL : $url;
        $targetFolder = ($targetFolder === '') ? $this->installationFolder : $targetFolder;
    }

    /**
     * Returns the pretty name of this component, e.g. "Xdebug".
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the registry name of a component, e.g. "phpext_xdebug" for "Xdebug".
     *
     * @return string
     */
    public function getRegistryName()
    {
        return $this->registryName;
    }

    /**
     * Returns the Version
     *
     * @return string
     */
    abstract public function getVersion();
}

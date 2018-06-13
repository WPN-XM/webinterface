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
 * Custom Autoloader
 *
 * @param string The classname to include.
 */
class Autoloader
{
	public static function init()
	{
		spl_autoload_register('Autoloader::autoloadSoftware');
		spl_autoload_register('Autoloader::autoloadSoftwarePHPExtension');
		//spl_autoload_register('Autoloader::autoloadPHPSoftware');
		spl_autoload_register('Autoloader::autoload');
	}
	
	public static function autoload($class)
	{
	    // return early, if class already loaded
	    if (class_exists($class, false)) {
	        return true;
	    }

	    // the project-specific namespace prefix
	    $prefix = 'WPNXM\Webinterface\\';

	    // is this a request for one of our classes, else exit early from this autoloader
		if(strpos($class, $prefix) === false) {
			return false;
		}	   

	    // base directory for the namespace prefix (normally "/src/")
	    $base_dir = __DIR__.DS.'src'.DS;

	    // does the class use the namespace prefix?
	    $len = strlen($prefix);
	    if (strncmp($prefix, $class, $len) !== 0) {
	        // no, move to the next registered autoloader
	        return;
	    }

	    // get the relative class name
	    $relative_class = substr($class, $len);

	    // replace the namespace prefix with the base directory,
	    // replace namespace separators with directory separators in the relative class name,
	    // append with .php
	    $file = $base_dir.str_replace('\\', DS, $relative_class).'.php';

	    // if the file exists, require it
	    if (file_exists($file) === true) {
	        require $file;
	    } else {
	        /*throw new \Exception(
	            sprintf('Autoloading Failure! Class "%s" requested, but file "%s" not found.', $class, $file)
	        );*/
	        return false;
	    }
	}

	/**
	 * Instantiate class for a software by name
	 * 
	 * Software, e.g. '\WPNXM\Webinterface\Software\Nginx'  
	 *
	 * @param  string $software
	 * @return object
	 */
	public static function autoloadSoftware($software)
	{		
		// return early, if class already loaded
	    if (class_exists($software, false)) {
	        return true;
	    }

		$software = strtolower($software);		
	    $nsArray  = explode('\\', $software);
	    $nsCount  = count($nsArray);

		// Nginx
	    if($nsCount === 1) {
	    	// software = software = $nsArray[0]
	    	$class = $software;
	    }
	    // \WPNXM\Webinterface\Software\Nginx
	    elseif($nsCount === 4) {
			$software  = $nsArray[3];   
		    $class     = $nsArray[3];
		}
		// \WPNXM\Webinterface\Software\Nginx\NginxConfig
		elseif(count($nsArray) === 5) { 
			$software  = $nsArray[3]; 
		    $class     = $nsArray[4]; 
		} else {
            return false;
        }
        
		$class = ucfirst($class);
	    $file = VENDOR_DIR . 'wpn-xm\software\\'.$software.'\scripts\webinterface\\'.$class.'.php';

	    if(is_file($file)) {
	    	include $file;
	    	return true;
		}   
	}

	/**
	 * Instantiate a PHP Extensions class from the Software repository
	 *
	 * PHP Extension, e.g. '\WPNXM\Webinterface\Software\PHPExtension\Xdebug'
	 *
	 * @param  string $software
	 * @return object
	 */
	public static function autoloadSoftwarePHPExtension($software)
	{		
		// return early, if class already loaded
	    if (class_exists($software, false)) {
	        return true;
	    }

		$software  = str_replace('WPNXM\Webinterface\Software\PHPExtension\\', '', $software);
	    $dir       = strtolower($software);
	    $class     = ucfirst($software);

	    $file = VENDOR_DIR . 'wpn-xm\software\PHPExtension\\'.$dir.'\scripts\webinterface\\'.$class.'.php'; 

	    if(is_file($file)) {
	    	include $file;
	    	return true;
		}
	}
}
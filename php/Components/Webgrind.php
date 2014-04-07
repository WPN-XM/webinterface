<?php

namespace Webinterface\Components;

/**
 * WPN-XM Webinterface - Class for XDebug
 */
class XDebug extends AbstractComponent
{
    public function getVersion()
    {
        ;
    }
    
    /**
     * Set the storage and profiler data folder to "webgrind/config.php".
     * The default folder is the php.ini value of "xdebug.profiler_output_dir".
     */
    public static function setProfilerDataDir($dir = '')
    {
        if($dir === '') { 
            $dir = ini_get('xdebug.profiler_output_dir');
        }
        
        $file = WPNXM_WWW_DIR . 'webgrind/config.php';

        $handle = @fopen($file, "r");
        if ($handle) {
            $line = 0;
            while (!feof($handle)) {
                $line++;
                $linebuffer = fgets($fh);
                // set storage dir by replacing line 19
                if ($line === 19) {
                    $linebuffer = '    static $storageDir = \'' . $dir . "'\n";
                    continue;
                }
                // set profiler dir by replacing line 20
                if ($line === 20) {
                    $linebuffer = '    static $profilerDir = \'' . $dir . "'\n";
                    continue;
                }
                $out .= $linebuffer;
            }
        }
        fclose($handle);
        
        return (bool) file_put_contents($file, $out);
    }

}
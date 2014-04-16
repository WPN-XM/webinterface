<?php
namespace Webinterface\Helper;

class Updater
{
    public static function updateRegistry()
    {
        // WPN-XM Software Registry - Latest Version @ GitHub
        $url = 'https://raw2.github.com/WPN-XM/registry/master/wpnxm-software-registry.php';

        // fetch date header
        stream_context_set_default(
            array(
                'http' => array(
                    'method' => 'HEAD'
                )
            )
        );

        // silenced: throws warning, if offline
        $headers = @get_headers($url);

        // offline
        if(empty($header)) {
            return false;
        }

        // parse header date
        $date_str = str_replace('Date: ', '', $headers[1]);
        $date = \DateTime::createFromFormat('D, d M Y H:i:s O', $date_str);
        $last_modified = filemtime(WPNXM_DATA_DIR . 'wpnxm-software-registry.php');

        // update condition, older than 1 week
        $needsUpdate = $date->getTimestamp() >= $last_modified + (7 * 24 * 60 * 60);

        // do update
        $updated = false;
        if($needsUpdate === true) {
           $updated = file_put_contents(WPNXM_DATA_DIR . 'wpnxm-software-registry.php', file_get_contents($url));
        }
        return $updated;
    }
}
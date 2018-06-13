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

class Downloader
{
    /**
     * Download a file as stream.
     *
     * @param string $url
     */
    public static function download($url)
    {
        $file = fopen(WPNXM_TEMP.basename($url), 'w+');

        set_time_limit(0); // unlimited max execution time

        $options = [
            CURLOPT_URL              => $url,
            CURLOPT_FILE             => $file,
            CURLOPT_TIMEOUT          => 3600 * 2, // set 2h to not timeout on big files
            CURLOPT_HEADER           => 0,
            CURLOPT_NOPROGRESS       => false,
            CURLOPT_SSL_VERIFYPEER   => false,
            CURLOPT_PROGRESSFUNCTION => 'curl_progress_callback',
            //CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_BUFFERSIZE     => 4096,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        curl_close($ch);

        fclose($file);
    }

    /**
     * Download $url to $file.
     * If the file doesn't exist, fetch it
     * If the file exist, download it only, when it is "old".
     *
     * @param  string $url  Source URL.
     * @param  string $file Target file.
     *
     * @return bool         True, when download successful.
     */
    public static function downloadIfNotExistsOrOld($url, $file)
    {
        if(!file_exists($file)) {
            $needsUpdate = true;
        } else {
            // fetch date header (doing a simple HEAD request)
            stream_context_set_default([
                'http' => [
                    'method' => 'HEAD',
                ],
            ]);

            // silenced: throws warning, if offline
            $headers = @get_headers($url, 1);

            // we are offline
            if (empty($headers) === true) {
                return false;
            }

            // parse header date
            $date          = \DateTime::createFromFormat('D, d M Y H:i:s O', $headers['Date']);
            $last_modified = filemtime($file);

            // update condition, older than 1 week
            $needsUpdate = $date->getTimestamp() >= $last_modified + (7 * 24 * 60 * 60);
        }

        // do update
        $updated = false;
        if ($needsUpdate === true) {

            // set request method back to GET, to fetch the file
            stream_context_set_default([
                'http' => [
                    'method' => 'GET',
                ],
            ]);

            $updated = file_put_contents($file, file_get_contents($url));
        }

        return $updated;
    }
}

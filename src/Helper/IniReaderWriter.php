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

/**
 * WPN-XM Server Stack - INI Reader and Writer.
 */
class IniReaderWriter
{
    /* @var array File content, line by line. */
    protected $lines;

    /* @var string The INI filename. */
    protected $file;

    public function __construct($file = '')
    {
        if (is_file($file) === false) {
            throw new \Exception(
                'The php.ini file could not be found. Check PHP folder for a "php.ini" file!'
            );
        }

        $this->file = $file;
        $this->read();
    }

    public function read()
    {
        $this->lines = [];

        $section = '';

        $content = file($this->file);

        foreach ($content as $line) {
            // comment or whitespace
            if (preg_match('/^\s*(;.*)?$/', $line)) {
                $this->lines[] = ['type' => 'comment', 'data' => $line];
            // section
            } elseif (preg_match('/\[(.*)\]/', $line, $match)) {
                $section       = $match[1];
                $this->lines[] = ['type' => 'section', 'data' => $line, 'section' => $section];
            // entry
            } elseif (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $line, $match)) {
                $this->lines[] = ['type' => 'entry', 'data' => $line, 'section' => $section, 'key' => $match[1], 'value' => $match[2]];
            }
        }

        return $this;
    }

    /**
     * @param string $section
     * @param string $key
     */
    public function get($section, $key)
    {
        if(!isset($section)) {
            throw new \Exception('Missing parameter $section.');
        }

        if(!isset($key)) {
            throw new \Exception('Missing parameter $key.');
        }

        foreach ($this->lines as $line) {
            if ($line['type'] !== 'entry') {
                continue;
            }

            if ($line['section'] != strtolower($section)) {
                continue;
            }

            if ($line['key'] != $key) {
                continue;
            } else {
                return $line['value'];
            }
        }

        throw new \Exception('Missing INI section or key.');
    }

    /**
     * @param string $section
     */
    public function set($section, $key, $value)
    {
        if(!isset($section)) {
            throw new \Exception('Missing parameter $section.');
        }

        if(!isset($key)) {
            throw new \Exception('Missing parameter $key.');
        }

        if(!isset($value)) {
            throw new \Exception('Missing parameter $value.');
        }

        foreach ($this->lines as &$line) {
            if ($line['type'] != 'entry') {
                continue;
            }
            //if($line['section'] != $section) continue;
            if ($line['key'] != $key) {
                continue;
            }
            $line['value'] = $value;
            $line['data']  = $key.' = '.$value."\r\n";

            return;
        }

        throw new \Exception('Missing Section or Key');
    }

    public function write($file = '')
    {
        if ($file == '') {
            $file = $this->file;
        }

        $fp = fopen($file, 'w');

        foreach ($this->lines as $line) {
            fwrite($fp, $line['data']);
        }

        fclose($fp);
    }

    public function returnArray()
    {
        return $this->lines;
    }
}

<?php
namespace Webinterface\Helper;
/**
 * INI Reader and Writer
 * The file content is in array $lines.
 */
class INIReaderWriter
{
    protected $lines;
    protected $file;

    public function __construct($file = '')
    {
        if ($file != '') {
            $this->file = $file;
            $this->read($file);
        }
    }

    public function read($file)
    {
        $this->lines = array();

        $section = '';

        foreach (file($file) as $line) {
            // comment or whitespace
            if (preg_match('/^\s*(;.*)?$/', $line)) {
                $this->lines[] = array('type' => 'comment', 'data' => $line);
            // section
            } elseif (preg_match('/\[(.*)\]/', $line, $match)) {
                $section = $match[1];
                $this->lines[] = array('type' => 'section', 'data' => $line, 'section' => $section);
            // entry
            } elseif (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $line, $match)) {
                $this->lines[] = array('type' => 'entry', 'data' => $line, 'section' => $section, 'key' => $match[1], 'value' => $match[2]);
            }
        }

        return $this;
    }

    public function get($section, $key)
    {
        foreach ($this->lines as $line) {
            if($line['type'] != 'entry') continue;
            //if($line['section'] != $section) continue;
            if($line['key'] != $key) continue;

            return $line['value'];
        }

        //throw new Exception('Missing Section or Key');
    }

    public function set($section, $key, $value)
    {
        foreach ($this->lines as &$line) {
            if($line['type'] != 'entry') continue;
            //if($line['section'] != $section) continue;
            if($line['key'] != $key) continue;
            $line['value'] = $value;
            $line['data'] = $key . " = " . $value . "\r\n";

            return;
        }

        throw new Exception('Missing Section or Key');
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

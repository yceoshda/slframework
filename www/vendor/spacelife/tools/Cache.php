<?php

namespace spacelife\tools;

use spacelife\exception\SLException;

/**
* Cache
*/
class Cache
{
    protected $directory = false;
    protected $ttl = false;
    protected $default_type = false;

    protected $now = false;

    public function __construct($cache_config)
    {
        if (!is_object($cache_config)) {
            throw new SLException("Invalid cache configuration", 1);
        }

        $this->directory = ROOT.DS.$cache_config->directory;
        $this->default_type = $cache_config->default_type;
        $this->ttl = $cache_config->ttl;
        $this->status = $cache_config->status;

        $this->now = date('U');
    }

    /**
    *   load
    *
    **/
    public function load($file, $ttl_override = false)
    {
        $ttl = $this->ttl;
        if (is_integer($ttl_override)) {
            $ttl = intval($ttl_override);
        }

        $filepath = $this->filepath($file);

        //  if the cache file exists
        if (file_exists($filepath)) {
            //  if it's to old
            if (($this->now - filemtime($filepath)) > $ttl) {
                unlink($filepath);
                return false;
            } else {
                $content = file_get_contents($filepath);
                if ($content == "") {
                    return false;
                }
                return $content;
            }
        } else {
            return false;
        }
    }

    /**
    *   save
    *
    **/
    public function save($file, $data)
    {
        $filepath = $this->filepath($file);

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        file_put_contents($filepath, $data, LOCK_EX);
    }

    /**
    *   filepath
    *
    **/
    protected function filepath($file)
    {
        //  remove unwanted ../
        $file = preg_replace('/\.\.\//', '', $file);
        $file = preg_replace('/[*]/', '', $file);
        $file = trim($file, DS);

        //  add default extension if not specified
        if (!preg_match('/\..+$/', $file)) {
            $file = $file.'.'.$this->default_type;
        }

        $filepath = $this->directory.DS.$file;

        return $filepath;
    }

    /**
    *   getList
    *
    **/
    public function getList()
    {
        $list = glob($this->directory.DS.'*');

        if (empty($list)) {
            return false;
        }
        $files = [];
        foreach ($list as $file) {
            $current = pathinfo($file);
            $current['duration'] = $this->duration($this->now - filemtime($file));
            $files[] = $current;
        }

        return $files;
    }

    /**
    *   duration
    *
    **/
    protected function duration($time)
    {
        $status = $this->status->good;
        if ($time > ($this->ttl * 0.9)) {
            $status = $this->status->old;
        }
        if ($time > $this->ttl) {
            $status = $this->status->expired;
        }
        $duration = [
            'ts'     => $time,
            'hms'    => intval($time / 3600).'h '.(intval(($time%3600) / 60)).'m '.intval(($time%60)).'s',
            'status' => $status
        ];

        return $duration;
    }

    /**
    *   delete
    *
    **/
    public function delete($file)
    {
        $filepath = $this->filepath($file);

        if (file_exists($filepath)) {
            return unlink($filepath);
        } else {
            return false;
        }
    }


}

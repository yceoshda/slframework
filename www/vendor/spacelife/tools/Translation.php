<?php

namespace spacelife\tools;

//  use
use spacelife\exception\SLException;
use spacelife\helper\JSON;

/**
* Translation
*/
class Translation
{
    //  config
    protected $config = false;

    //  translation
    protected $translation = false;

    //  config
    protected $ext = '.json';

    //  usefull vars
    protected $path = false;
    protected $lang = false;
    protected $file = false;

    //  protected cache object
    protected $cache = false;

    /**
    *   Constructor
    *       does everything !
    */
    public function __construct(array $params, $config, $cache = false)
    {
        $this->config = $config;
        $this->cache = $cache;

        //  setting translation path
        if (isset($params['path']) && $params['path'] !== false) {
            $this->path = $params['path'];
        } else {
            $this->path = $this->config->translationPath;
        }
        //  setting translation lang
        if (isset($params['lang']) && $params['lang'] !== false) {
            $this->lang = $params['lang'];
        } else {
            $this->lang = $this->config->default_lang;
        }

        //  setting file to load
        if (!isset($params['file'])) {
            throw new SLException("Error translation file not specified", 500);
        } else {
            $this->file = $params['file'];
        }

        //  filename for requested lang (to ask cache)
        $lang_filename = $this->file.'_'.$this->lang;
        $from_cache = $this->cache->load($lang_filename);

        if ($from_cache !== false) {
            $this->translation = JSON::decode($from_cache);
        } else {
            if (substr($this->file, 0, 1) == DS) {
                $default_file = ROOT.DS.$this->config->translationPath.DS.$this->file.$this->ext;
                $lang_file = ROOT.DS.$this->config->translationPath.DS.$this->file.'_'.$this->lang.$this->ext;
                $default_lang = ROOT.DS.$this->config->translationPath.DS.$this->file.'_'.$this->config->default_lang.$this->ext;
            } else {
                //  normal loading
                $default_file = ROOT.DS.$this->path.DS.$this->file.$this->ext;
                $lang_file = ROOT.DS.$this->path.DS.$this->file.'_'.$this->lang.$this->ext;
                $default_lang = ROOT.DS.$this->path.DS.$this->file.'_'.$this->config->default_lang.$this->ext;
            }
            // $default_lang_filename = $this->file.'_'.$this->config->default_lang.$this->ext;

            $default_data = $this->loadFile($default_file);
            try {
                $lang_data = $this->loadFile($lang_file);
            } catch (SLException $e) {
                try {
                    $lang_data = $this->loadFile($default_lang);
                    // $lang_filename = $default_lang_filename;
                } catch (SLException $e) {
                    $lang_data = [];
                }
            }

            $translation_data = $this->merge($default_data, $lang_data);

            $this->translation = JSON::decode(json_encode($translation_data));
            if ($this->translation === false) {
                throw new SLException("Error decoding final translation {$this->file}", 501);
            }

            //  save it to cache
            $this->cache->save($lang_filename, json_encode($this->translation));
        }

    }

    /*
    *   get
    *       magic method to retrieve translated items
    */
    public function get()
    {
        return $this->translation;
    }

    /*
    *   loadFile
    *       loads a translation file
    */
    protected function loadFile($file)
    {
        if (file_exists($file)) {
            $decode = JSON::decode(file_get_contents($file), true);
            if ($decode === false) {
                throw new SLException("Error decoding translation file $file", 501);
            }
            return $decode;
        } else {
            throw new SLException("Error loading translation file $file", 404);
        }
    }

    /*
    *   merge
    *       merges default and language specific translation
    */
    protected function merge($default, $language)
    {
        $keys = array_keys( $language );
        foreach( $keys as $key ) {
            if( isset( $default[$key] )
                && is_array( $default[$key] )
                && is_array( $language[$key] )
            ) {
                $default[$key] = $this->merge( $default[$key], $language[$key] );
            } else {
                $default[$key] = $language[$key];
            }
        }

        return $default;
    }
}
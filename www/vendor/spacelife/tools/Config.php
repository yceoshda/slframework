<?php

namespace spacelife\tools;

use spacelife\tools\Collection;
use spacelife\exception\SLException;
use spacelife\helper\JSON;

/**
* Config
*/
class Config
{
    //  this will hold the computed config
    protected $config = false;

    //  where to load
    protected $config_path = false;

    //  where to cache
    protected $config_cache = false;

    //  what env to load
    protected $config_env = false;

    public function __construct($path = 'config', $env = 'prd')
    {
        $this->config_path = ROOT.DS.$path;
        $this->config_cache = ROOT.DS.'cache'.DS.'config_cache.json';
        $this->config_env = $env;

        $this->config = $this->loadConfig();
    }

    /**
    *   get
    *
    **/
    public function get($key)
    {
        if (isset($key)) {
            return $this->config->$key;
        } else {
            return null;
        }
    }

    /**
    *   __get
    *
    **/
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
    *   loadConfig
    *
    **/
    protected function loadConfig()
    {
        //  if cache file exists ... load it
        if (file_exists($this->config_cache)) {
            $config = $this->loadFromCache();

            //  if cache loading was successfull
            if ($config !== false) {
                return $config;
            }
        }

        //  load base config
        $base_file = $this->config_path.DS.'config_default.json';
        if (file_exists($base_file)) {
            $base_config = JSON::decode(file_get_contents($base_file), true);

            //  if could not load base config ... throw !
            if ($base_config === false) {
                throw new SLException("Error loading base configuration", 403);
            }
        } else {
            throw new SLException("Error missing base configuration file", 404);
        }

        //  load custom config
        $custom_file = $this->config_path.DS.'config.json';
        if (file_exists($custom_file)) {
            $custom_config = JSON::decode(file_get_contents($custom_file), true);

            //  check if valid
            if ($custom_config === false) {
                throw new SLException("Error loading custom config file", 403);
            }
        } else {
            $custom_config = [];
        }

        //  merge default and custom
        $merged_config = $this->merge($base_config, $custom_config);

        //  jsonize merged config
        $result_config = JSON::encode($merged_config);

        //  put in cache
        file_put_contents($this->config_cache, $result_config);

        return json_decode($result_config);
    }

    /**
    *   loadFromCache
    *
    **/
    protected function loadFromCache()
    {
        $config = JSON::decode(file_get_contents($this->config_cache));

        if ($config === false || $config === null) {
            unlink($this->config_cache);
            return false;
        }
        return $config;
    }

    /*
    *   merge
    *       merges default and custom configs
    */
    protected function merge($default, $custom)
    {
        $keys = array_keys( $custom );
        foreach( $keys as $key ) {
            if( isset( $default[$key] )
                && is_array( $default[$key] )
                && is_array( $custom[$key] )
            ) {
                $default[$key] = $this->merge( $default[$key], $custom[$key] );
            } else {
                $default[$key] = $custom[$key];
            }
        }

        return $default;
    }

}
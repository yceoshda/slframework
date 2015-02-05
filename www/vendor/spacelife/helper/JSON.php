<?php

namespace spacelife\helper;

/**
* JSON
*/
class JSON
{
    /**
    *   decode
    *
    **/
    public static function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        // search and remove comments like /* */ and //
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
        //  search and remove trailling commas
        $json = preg_replace('/,\s*(}|])/', '\1', $json);

        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $json = json_decode($json, $assoc, $depth, $options);
        }
        elseif (version_compare(phpversion(), '5.3.0', '>=')) {
            $json = json_decode($json, $assoc, $depth);
        }
        else {
            $json = json_decode($json, $assoc);
        }

        return $json;
    }

    /**
    *   encode
    *
    **/
    public static function encode($value ,$options = 0 ,$depth = 512)
    {
        return json_encode($value);
    }


}
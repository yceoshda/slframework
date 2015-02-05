<?php

namespace spacelife\helper;

use spacelife\core\Router;
/**
* HTML
*/
class HTML
{

    /*
    *   js
    *       transform js list in html tags
    */
    public static function js($data)
    {
        $js_tags = '';

        //  if there is a single JS passed as a string
        if (is_string($data)) {
            $js_tags = self::jsTag($data);
        }

        //  if there are 1 or more js passed as an array
        if (is_array($data)) {
            foreach ($data as $js) {
                $js_tags .= self::jsTag($js);
            }
        }

        return $js_tags;
    }

    /*
    *   jsTag
    *       creates an html for JS
    */
    protected static function jsTag($js)
    {
        if (is_array($js) && isset($js['inline'])) {
            $tag = '<script>'.$js['code'].'</script>';
        } else {
            if (substr($js, strlen($js) - 3) != '.js') {
                $file = $js.'.js';
            }

            $tag = '<script type="text/javascript" src="'.Router::webroot('js/'.$file).'"></script>';
        }

        return $tag;
    }

    /*
    *   css
    *       transform css list in html tags
    */
    public static function css($data)
    {
        $css_tags = '';

        //  if there is a single JS passed as a string
        if (is_string($data)) {
            $css_tags = self::cssTag($data);
        }

        //  if there are 1 or more js passed as an array
        if (is_array($data)) {
            foreach ($data as $css) {
                $css_tags .= self::cssTag($css);
            }
        }

        return $css_tags;
    }

    /*
    *   cssTag
    *       creates an html tag for css
    */
    protected static function cssTag($css)
    {
        if (substr($css, strlen($css) - 4) != '.css') {
            $file = $css.'.css';
        }

        $tag = '<link rel="stylesheet" href="'.Router::webroot('css/'.$file).'" type="text/css">';

        return $tag;
    }
}

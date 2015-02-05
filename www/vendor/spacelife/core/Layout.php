<?php
namespace spacelife\core;

/**
* Layout
*
*   Accessorized layout / template engine by SC for SC with multilang support
*   WARNING this class relies on SC/SL framework to work properly !
*
*   Framework configuration is loaded in the view scope in $_config
*/
// use \Config;
use spacelife\exception\SLException;

class Layout
{
    protected $html = '';

    protected $debug = 0;

    protected $vars = array();

    protected $config = false;

    /**
    *   __construct
    *
    **/
    public function __construct($file, $params)
    {
        $template_dir = ROOT.DS.'views'.DS;

        // if (substr($template_dir, strlen($template_dir) - 1) != DS) {
        //     $template_dir .= DS;
        // }

        //  storing filename without lang
        $file_no_lang = $file;

        if (!isset($params['lang']) || !isset($params['config'])) {
            throw new SLException("Error invalid parameters array", 500);

        }
        $lang = $params['lang'];
        $this->config = $params['config'];
        $default_lang = $this->config->default_lang;

        // die($lang);
        //  file name with language
        $file_lang = $file.'_'.$lang;

        $path = $template_dir.$this->addExt($file_lang);
        $path_no_lang = $template_dir.$this->addExt($file_no_lang);
        $path_default_lang = $template_dir.$this->addExt($file.'_'.$default_lang);

        //  finding a template file
        //  trying file with current lang
        //  then trying file with default lang
        //  then trying file without lang
        if (!file_exists($path)) {
            if (!file_exists($path_default_lang)) {
                if (!file_exists($path_no_lang)) {
                    throw new SLException("Template file $file_no_lang not found", 404);

                    // return $this->error('File not found '.$path_no_lang, 404);
                }
                $this->path = $path_no_lang;
            } else {
                $this->path = $path_default_lang;
            }
        } else {
            $this->path = $path;
        }

        //  inject config in render scope
        $this->set('_config', $this->config);

        //  inject translations in render scope
        if (isset($params['translate']) && is_object($params['translate'])) {
            $this->set('_translate', $params['translate']);
        }

        //  inject lang in render scope
        $this->set('_lang', $lang);
    }

    /*
    *   addExt
    *       adds the php extension if needed
    */
    protected function addExt($file)
    {
        if (substr($file, strlen($file) - 4) != '.php') {
            return $file.'.php';
        }
        return $file;
    }

    /**
    *   set
    *
    **/
    public function set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
    *   unset
    *
    *   unsets the specified variable
    **/
    public function remove($name)
    {
        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
        }
    }

    /**
    *   reset
    *
    *   reset the current layout (removes all variables and rendered elements)
    **/
    public function reset()
    {
        $this->vars = array();
        $this->set('config', $this->config);
        $this->html = '';
    }

    /**
    *   render
    *
    **/
    public function render($options = array())
    {
        extract($this->vars);
        ob_start();
        require $this->path;
        $this->html = ob_get_clean();

        if (isset($options['echo']) && $options['echo'] == true) {
            echo $this->html;
        } else {
            return $this->html;
        }
        return 0;
    }

    /**
    *   error
    *
    **/
    protected function error($message, $code, $kill = true)
    {
        // addSessionNotice(
        //     array(
        //         'message' => 'Layout Error: '.$message
        //         , 'message_type' => 'error')
        //     );
        if ($this->debug > 0 || $kill === true) {
            echo "Error: $code $message";
            die();
        }
    }

}
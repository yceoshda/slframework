<?php
namespace spacelife\core;
//  Main controller class

use spacelife\core\Layout;
use spacelife\core\Router;
use spacelife\core\Session;
use spacelife\helper\HTML;
use spacelife\helper\Form;
use spacelife\tools\Translation;
use spacelife\tools\Collection;
use spacelife\tools\Config;
use spacelife\tools\Cache;

/**
* Controller
*   This class will be inherited by all the "real" controllers
*/
class Controller
{
    //  request
    protected $request = false;

    //  config
    protected $config = false;

    //  layout (with default, in case the controller doesnot set one specifically)
    protected $layout = '/layout/default';
    protected $admin_layout = '/layout/admin_default';

    //  templatization (default template dir for Layout objects)
    protected $template_dir = false;

    //  default models namespace
    protected $modelNS = false;

    //  model autoloader
    protected $models = false;

    //  variables to send to the layout
    protected $content_vars = array(
        'content'     => '',
        'message'     => '',
        'title'       => '',
        'js'          => array(),
        'css'         => array(),
        'api_content' => false
        );

    //  session
    protected $session = false;

    //  lang
    protected $lang = false;

    //  translation
    protected $translation_path = false;
    protected $translate = false;
    protected $translate_admin = false;
    protected $translate_layout = 'layout';
    protected $translate_layout_dir = '';

    //  api mode
    protected $api_mode = false;

    //  admin mode
    protected $admin_mode = false;

    //  caching tool (pseudo singleton)
    protected $cache = false;

    /*
    *   Contructor
    *       injecting request in the current object
    */
    public function __construct($request)
    {
        //  request object
        $this->request = $request;

        //  config
        $this->config = isset($this->request->config) ? $this->request->config : new Config();
        unset($this->request->config);

        //  cache init
        $this->cache = new Cache($this->config->cache);

        //  create / retrieve session
        $this->session = new Session($this->config);

        //  multilang
        $this->setLang();

        //  check for login
        if (isset($this->request->authreq) && $this->request->authreq === true) {
            if ($this->session->isLoggued() === false) {
                $this->redirect('user/login');
            }
        }

        //  check for "need password change"
        if (isset($_SESSION['chpassword']) && $_SESSION['chpassword'] === true) {
            //  if route is NOT for changing password
            if ($this->request->controller != $this->config->changePassword->controller && $this->request->action != $this->config->changePassword->controller) {
                $this->redirect('user/changePassword');
            }
        }

        //  layout translation
        $translate_layout = $this->translate_layout;
        // $translate_layout_dir = $this->config->translation_path;
        $translate_layout_dir = $this->translate_layout_dir;

        //  look for prefixes
        if (isset($this->request->prefix)) {
            switch ($this->request->prefix) {

                //  if requested action is admin bound change default layout and title
                case 'admin':
                    //  check that the user isauthorized
                    if (!$this->session->isAdmin()) {
                        $this->error('Not Authorized');
                    }
                    $this->setLayout($this->admin_layout);
                    $this->content_vars['title'] = $this->config->admin_title;
                    $translate_layout = $this->request->prefix.'_'.$translate_layout;
                    $this->admin_mode = true;
                    break;

                //  if its api ... set api mode on
                case 'api':
                    $this->setLayout('/layout/api');
                    $this->api_mode = true;
                    break;

                default:
                    break;
            }
        }

        //  load layout translation
        $translation = $this->loadTranslation($translate_layout_dir.DS.$translate_layout);
        //  catching api mode from request type
        if ($this->request->server->ajax === true) {
            $this->setLayout('/layout/api');
            $this->api_mode = true;
        }

        //  logs
        // $this->Logger = new Log($this->config->log);

        //  model autoloader
        if ($this->models && is_array($this->models)) {
            foreach ($this->models as $key => $value) {
                if (is_integer($key)) {
                    // die($value);
                    $this->loadModel($value);
                } else {
                    $this->loadModel($key, $value);
                }

            }
        }

        //  translations autoloader
        if ($this->translate && is_array($this->translate)) {
            foreach ($this->translate as $translation) {
                $this->loadTranslation($translation);
            }
        }
        if ($this->admin_mode) {
            if ($this->translate_admin && is_array($this->translate_admin)) {
                foreach ($this->translate_admin as $translation) {
                    $this->loadTranslation($translation);
                }
            }
        }

    }

    /*
    *   setLayout
    *       override default layout for final rendering
    */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /*
    *   set
    *       setting a variable to be displayed with the layout
    */
    public function set($key, $value)
    {
        $this->content_vars[$key] = $value;
    }

    /*
    *   setContent
    *       sets the content for the layout
    */
    public function setContent($content)
    {
        if ($this->api_mode === true) {
            $this->set('api_content', $content);
        } else {
            $this->set('content', $content);
        }
    }

    /*
    *   setTitle
    *       sets the title for layout
    */
    public function setTitle($title)
    {
        $this->set('title', $title);
    }

    /**
    *   setApiContent
    *
    **/
    public function setApiContent($data)
    {
        $this->set('api_content', $data);
    }


    /*
    *   addJS
    *       add javascript helper
    */
    public function addJS($js)
    {
        $this->content_vars['js'][] = $js;
    }

    /*
    *   addCSS
    *       add CSS helper
    */
    public function addCSS($css)
    {
        $this->content_vars['css'][] = $css;
    }

    /*
    *   render
    *       renderring page using layout
    */
    public function render($retval = false)
    {

        //  if api mode is active
        if ($this->api_mode) {
            $this->apiRender();
        }
        unset($this->content_vars['api_content']);

        //  templatizing layout
        // $tpl = new Layout($this->layout, ['lang' => $this->lang, 'config' => $this->config]);
        $tpl = $this->template($this->layout);

        //  parse and create notifications
        $this->notify();

        //  Google Analytics integration
        $ga = '';
        if ($this->config->googleAnalytics->enabled) {
            if ($this->config->googleAnalytics->cnil) {
                $this->addJS('jquery.cookie.min');
            }
            $tplga = $this->template('/layout/ga');
            $ga = $tplga->render();
        }
        $tpl->set('googleanalytics', $ga);

        //  calling helper on JS
        $js = HTML::js($this->content_vars['js']);
        unset($this->content_vars['js']);

        //  calling helper on CSS
        $css = HTML::css($this->content_vars['css']);
        unset($this->content_vars['css']);

        //  injecting javascripts and css
        $tpl->set('js_inject', $js);
        $tpl->set('css_inject', $css);

        //  setting page title (default to site config title)
        if ($this->content_vars['title'] == '') {
            $this->setTitle($this->config->title);
        }

        //  injecting all variables
        foreach ($this->content_vars as $key => $value) {
            $tpl->set($key, $value);
        }

        //  sending session to view
        $tpl->set('session', $this->session);

        //  rendering layout
        $html = $tpl->render();

        //  returns or display output
        if ($retval === false) {
            echo $html;
        } else {
            return $html;
        }
    }

    /**
    *   apiRender
    *       specific render method for api (json content)
    **/
    protected function apiRender()
    {
        header('Cache-control: no-cache, must-revalidate');
        header('Content-type: application/json');
        echo json_encode($this->content_vars['api_content']);
        die();
    }


    /**
    *   notify
    *
    **/
    protected function notify()
    {
        $notifs = $this->session->notify();
        $html = '';
        if ($notifs) {
            $tpl = new Layout('layout/notice', ['lang' => $this->lang, 'config' => $this->config]);
            // foreach ($notifs as $notif) {
                $tpl->set('notices', $notifs);
                // $tpl->set('message', $notif['message']);
                $html .= $tpl->render();
                // $tpl->reset();
            // }
            $this->addJS(['inline' => true, 'code' => $html]);
        }
        return $html;
    }


    /*
    *   loadModel
    *       Model classes injections
    */
    public function loadModel($model, $nsmodel = '')
    {
        $model = ucfirst($model);
        if (!isset($this->$model)) {
            if ($nsmodel != '') {
                $this->$model = new $nsmodel($this->request->db, $this->config);
            } else {
                $modelname = $model.'Model';
                if ($this->modelNS !== false) {
                    $nsmodel = $this->modelNS.'\\'.$modelname;
                    $this->$model = new $nsmodel($this->request->db, $this->config);
                } else {
                    $this->$model = new $modelname($this->request->db, $this->config);
                }
            }
        }
    }

    /*
    *   loadTranslation
    *       loads the json file used to translate
    */
    public function loadTranslation($file, $force = false)
    {
        //  translations will be loaded in the controller property T with the name of the file
        if (!isset($this->T)) {
            $this->T = new \stdClass();
        }
        $translation = trim($file, DS);

        //  if it was already loaded ... don't bother
        if (isset($this->T->$translation) && $force === false) {
            return $translation;
        }

        $translator = new Translation(['file' => $file, 'path' => $this->translation_path, 'lang' => $this->lang], $this->config, $this->cache);

        $this->T->$translation = new Collection($translator->get());

        return $translation;
    }

    /*
    *   redirect
    *       redirection method
    */
    public function redirect($url, $code = null )
    {
        if($code == 301){
            header("HTTP/1.1 301 Moved Permanently");
        }

        //  if code -1 then just redirect without routing
        if ($code == -1) {
            header("Location: $url");
            die();
        }

        header("Location: ".Router::url($url));
        die();
    }

    /*
    *   e404
    *       handler for Not Found (method)
    */
    public function e404($message)
    {
        header("HTTP/1.1 404 Not Found");
        $this->template_dir = false;
        $tpl = $this->template('errors/404');
        $this->loadTranslation('e404');
        if (isset($this->request->url)) {
            $tpl->set('request', $this->request->url);
        } else {
            $tpl->set('request', 'lost ?');
        }
        $tpl->set('message', $this->T->e404->$message);
        $this->set('content', $tpl->render());
        $this->render();
        die();
    }

    /*
    *   request
    *       controller injector
    */
    public function request($controller, $action)
    {
        // $controller .= 'Controller';
        // require_once ROOT.DS.'controller'.DS.$controller.'.php';
        $c = new $controller($this->request);
        return $c->$action();
    }

    /*
    *   setLang
    *       set lang to render things
    */
    protected function setLang($preset = false)
    {
        //  if lang is specified in the url
        if (isset($this->request->uri->lang)) {
            $lang = preg_replace('/[^a-z]/', '', $this->request->uri->lang);
        } else {
            //  if lang is specified in the session
            if ($this->session->lang) {
                $lang = preg_replace('/[^a-z]/', '', $this->session->lang);
            } else {
                //  if lang is found in the browser variables
                if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                } else {
                    //  defaulting to site default
                    $lang = $this->config->default_lang;
                }
            }
        }

        //  if we try to set it forcefully
        if ($preset !== false) {
            $lang = $preset;
        }

        //  making sure the lang is indeed coverred !
        if (in_array($lang, $this->config->langs)) {
            $this->lang = $lang;
        } else {
            //  else defaulting
            $this->lang = $this->config->default_lang;
        }

        //  adding it to session
        $this->session->set('lang', $this->lang);
    }

    /*
    *   Log
    *       logs whatever
    */
    public function Log($data, $sender = '')
    {
        //  log only if logger is enabled
        if ($this->config->log->enable === true) {
            //  if sender is not set, try to get class name
            if ($sender == '') {
                if (isset($this->classname)) {
                    $sender = $this->classname;
                } else {
                    $sender = get_class($this);
                }
            }
            $this->Logger->write($data, $sender);
        }
    }

    /**
    *   template
    *       load automatically a template with lang support
    **/
    public function template($file, $lang = null)
    {
        if (substr($file, 0, 1) != '/') {
            if (isset($this->template_dir) && $this->template_dir !== false) {
                $file = $this->template_dir.DS.$file;
            }
        }

        $lang = $lang == null ? $this->session->lang : $lang;

        return new Layout($file, ['lang' => $lang, 'config' => $this->config, 'translate' => (isset($this->T) ? $this->T : false)]);
    }

    /**
    *   loadTemplate
    *       alias for $this->template();
    **/
    public function loadTemplate($file, $lang = null)
    {
        return $this->template($file, $lang);
    }

    /**
    *   createForm
    *
    **/
    public function createForm($data, $errors, $translate_scope = false)
    {
        $params = [
            'data'         => $data,
            'errors'       => $errors,
            'translation'  => ($translate_scope ? $this->T->$translate_scope->fields : false),
            'btn'          => ($translate_scope ? $this->T->$translate_scope->btn : false),
            'config'       => $this->config,
            'session_csrf' => $this->session->csrf(),
        ];

        return new Form($params);
    }


    /*
    *   error
    *       error handling
    */
    public function error($message)
    {
        $tpl = new Layout('errors/error', ['lang' => $this->lang, 'config' => $this->config]);
        $tpl->set('message', $message);
        $this->set('content', $tpl->render());
        $this->render();
        die();
    }
}

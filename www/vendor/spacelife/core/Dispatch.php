<?php
namespace spacelife\core;

use spacelife\core\iConnect;
use spacelife\core\iConnect_PDO;
use spacelife\core\Request;
use spacelife\core\Router;
use spacelife\core\Controller;

/**
* GameDispatch
*/
class Dispatch
{
    protected $request = false;

    protected $config = false;

    public function __construct($config)
    {
        $this->config = $config;

        $db = new iConnect_PDO($this->config);

        //  request object
        $this->request = new Request($db);

        //  inject config in request
        $this->request->config = $config;

        //  parsing request
        Router::parse($this->request->url, $this->request);

         //  loading controller
        $ctl = $this->loadController();
        //  action to execute on the controller
        $action = $this->request->action;

        //  prefix
        if($this->request->prefix !== false) {
            $action = $this->request->prefix.'_'.$action;
        }

        //  checking if method exists
        if (!in_array($action, array_diff(get_class_methods($ctl), get_class_methods('spacelife\core\Controller')))) {
            $this->error('message');
        }
        //  calling controller method
        call_user_func_array(array($ctl, $action), $this->request->params);

        //  rendering page with layout
        $ctl->render();

    }

    /*
    *   loadController
    *       loading a controller
    */
    protected function loadController()
    {
        $ns = Router::nsMatch($this->request->controller);

        $name = $ns.'\\'.ucfirst($this->request->controller).'Controller';

        //  if we restrict only to routed controllers
        if ($this->config->restrict_to_routes) {
            if (Router::ctlSearch($this->request->controller)) {
                $controller = new $name($this->request);
                return $controller;
            } else {
                $controller = new Controller($this->request);
                $controller->e404('message');
            }
        }

        $controller = new $name($this->request);
        return $controller;

    }

    /*
    *   error
    *       sends back to controller error
    */
    protected function error($message)
    {
        $controller = new Controller($this->request);
        $controller->e404($message);

        // echo $message;
        // exit(1);
    }

}

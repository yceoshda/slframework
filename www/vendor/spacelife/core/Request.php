<?php
namespace spacelife\core;

/**
* Request
*/
class Request
{
    //  Url to handle
    public $url;
    //  is authentication required for this?
    public $authreq = false;
    //  prefix (i.e. for admin)
    public $prefix = false;
    //  data posted
    public $data = false;
    //  db connection
    public $db = false;
    //  full server vars
    public $server = false;


    public function __construct($db)
    {
        $this->url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

        // $this->getMainVars();
        $this->server = new \stdClass();
        $this->server->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $this->server->protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
        $this->server->ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false;

        if (!empty($_GET)) {
            $this->uri = new \stdClass();
            foreach ($_GET as $key => $value) {
                $this->uri->$key = $value;
            }
        }

        //  if $_POST: inject values into $this->data
        if (!empty($_POST)) {
            $this->data = new \stdClass();
            foreach ($_POST as $key => $value) {
                $this->data->$key = $value;
            }
        }

        //  catching artificially set server method
        if (isset($this->data->_method)) {
            $this->server['method'] = $this->data->_method;
        }

        $this->db = $db;
    }

}
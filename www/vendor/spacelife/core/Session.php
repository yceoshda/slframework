<?php
namespace spacelife\core;

use spacelife\core\Controller;

/**
* Session
*/
class Session
{
    protected $config = false;

    public function __construct($config)
    {
        $this->config = $config;

        if (!isset($_SESSION['login'])) {
            session_name($this->config->session->name);
            session_start();
        }
    }

    /*
    *   __set
    *       sets a session variable
    */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /*
    *   set
    *       sets a session variable
    */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /*
    *   __get
    *       gets a session variable
    */
    public function __get($key)
    {
        return $this->get($key);
    }

    /*
    *   get
    *       gets a session variable
    */
    public function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return false;
    }

    /*
    *   setFlash
    *       sets a flash notification for future use
    */
    public function setFlash($data = array())
    {
        $flash = [];
        if (is_string($data)) {
            $flash['message'] = $data;
        } else {
            if (!isset($data['message'])) {
                return false;
            }
            $flash['message'] = $data['message'];
        }
        if (!isset($data['type'])) {
            $flash['type'] = 'info';
        } else {
            $flash['type'] = $data['type'];
        }
        if (!isset($data['timeout'])) {
            $flash['timeout'] = 5;
        } else {
            $flash['timeout'] = $data['timeout'];
        }

        //  catching 'error' keyword to be replaced by danger for TWBS like styles
        $flash['type'] = $flash['type'] == 'error' ? 'danger' : $flash['type'];

        $_SESSION['notification'][] = $flash;
    }

    /*
    *   isLoggued
    *       check if the current request is performed loggued in
    */
    public function isLoggued()
    {
        if (!isset($_SESSION['login'])) {
            return false;
        }
        return true;
    }

    /*
    *   isAdmin
    *       check if the current user is admin
    */
    public function isAdmin()
    {
        if (isset($_SESSION['is_admin'])) {
            if ($_SESSION['is_admin'] === true) {
                return true;
            }
        }
        return false;
    }

    /*
    *   notify
    *       retrieves flash notifications and sends them to user
    */
    public function notify()
    {
        if (isset($_SESSION['notification']) && is_array($_SESSION['notification'])) {
            $notif = $_SESSION['notification'];
            unset($_SESSION['notification']);
            return $notif;
        } else {
            return false;
        }
    }

    /*
    *   csrf
    *       generates and return CSRF token
    */
    public function csrf()
    {
        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = sha1(date('U').rand(0, 100));
        }
        return $_SESSION['csrf'];
    }

    /*
    *   checkCsrf
    *       check CSRF
    */
    public function checkCsrf($data = false)
    {
        if (!$data) {
            if (isset($_POST['csrf'])) {
                $data = preg_replace('/[^a-f0-9]/', '', $_POST['csrf']);
            } elseif (isset($_GET['csrf'])) {
                $data = preg_replace('/[^a-f0-9]/', '', $_GET['csrf']);
            } else {
                $data = '';
            }
        } else {
            $data = preg_replace('/[^a-f0-9]/', '', $data);
        }

        if ($data == $this->csrf()) {
            return true;
        } else {
            $ctl = new Controller(false);
            $ctl->Log('CSRF error, unauthorized attempt to access someones session, diverted to parking url');
            $ctl->error('CSRF');
        }
    }

    /*
    *   login
    *       create login env
    */
    public function login($data)
    {
        $_SESSION['user_id'] = $data->id;
        $_SESSION['login'] = $data->login;

        //  password change required
        $_SESSION['chpassword'] = isset($data->chpassword) ? $data->chpassword : false;

        //  password expiration warning
        $_SESSION['warn_expire'] = isset($data->warn_expire) ? $data->warn_expire : false;

        //  admin
        $_SESSION['is_admin'] = $data->is_admin == 1 ? true : false;

        //  authentication type
        $_SESSION['auth_type'] = isset($data->auth_type) ? $data->auth_type : false;

        //  first_login
        if (isset($data->first_login) && $data->first_login === true) {
            $this->set('first_login', 1);
        }

        //  language
        $_SESSION['lang'] = isset($data->lang) ? $data->lang : $this->config->default_lang;
    }

    /*
    *   logout
    *       logs the user out (destroying session)
    */
    public function logout()
    {
        session_destroy();
        session_start();
    }
}

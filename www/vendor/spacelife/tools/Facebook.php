<?php

namespace spacelife\tools;

use spacelife\exception\SLException;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;

class Facebook
{

    protected $appId = false;
    protected $appSecret = false;
    protected $session_slf = false;

    /**
     * Constructor
     * @param Spacelife\Session $session_slf SLF session object
     * @param Spacelife\Config $config SLF configuration object (limited to facebook scope)
     */
    public function __construct($session_slf, $config)
    {
        $this->session_slf = $session_slf;
        $this->appId = $config->appId;
        $this->appSecret = $config->appSecret;

        //  prep Facebook api
        FacebookSession::setDefaultApplication($this->appId, $this->appSecret);

    }


    /**
     * use facebook connect
     * @param  string $redirect_url Url to redirect to after FB authentication
     * @return string|Facebook\GraphUser Returns a string (url to redirect to for FB connect) or an object GraphUser (FB user profile)
     */
    public function connect($redirect_url)
    {
        $helper = new FacebookRedirectLoginHelper($redirect_url);

        if ($this->session_slf->fb_token !== false) {
            $session = new FacebookSession($this->session_slf->fb_token);
        } else {
            $session = $helper->getSessionFromRedirect();
        }

        if ($session) {

            try {
                $this->session_slf->fb_token = $session->getToken();
                $request = new FacebookRequest($session, 'GET', '/me');
                $profile = $request->execute()->getGraphObject('Facebook\GraphUser');

                if ($profile->getEmail() === null) {
                    throw new SLException('Email is unavalaible');
                }

                return $profile;

            } catch (\Exception $e) {

                $this->session_slf->fb_token = false;

                return $helper->getReRequestUrl(['email']);

            }

        } else {

            return $helper->getLoginUrl(['email']);

        }
    }



}

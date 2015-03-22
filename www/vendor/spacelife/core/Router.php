<?php
namespace spacelife\core;

/**
* Classe de routing à utiliser pour un MVC
* Permet de rediriger des URLs vers des URL au format MVC controller/action/param/param/param....
* Router::prefix('cockpit','admin');
* Router::connect('','posts/index');
* Router::connect('cockpit','cockpit/posts/index');
* Router::connect('blog/:slug-:id','posts/view/id:([0-9]+)/slug:([a-z0-9\-]+)');
* Router::connect('blog/*','posts/*');
*
* Notes SpaceLife:
*   Merci Grafikart pour cette classe (www.grafikart.fr)
*
*   Mises a jour SpaceLife:
*   Added: possibilite de passer un troisieme parametre a connect pour exiger / desactiver l'authentification sur la route
*       Router::connect('url', 'route', true);
*   Added: define and retrieve namespace for controller
*       Router::nsDefine('controller', 'namespace');
*       Router::nsMatch('controller');
*   Added: building a controller list while connecting routes
*   Added: ctlSearch to determine wether or not a route is pointing to it
*       Router::ctlSearch('controller');
*   Changed: changed str_replace by preg_replace to prevent replacing prefixes words other than first part of url (self::url() method)
*   Changed: connect parameters handling, /? to / because params will always be with a / before
**/
class Router{


    protected static $routes = array();
    protected static $prefixes = array();
    protected static $nsTable = array();
    protected static $controllers = array();
    protected static $default_action = 'index';
    protected static $default_ns = 'spacelife';
    protected static $default_auth = true;

    /**
    * Ajoute un prefix au Routing
    **/
    public static function prefix($url,$prefix)
    {
        self::$prefixes[$url] = $prefix;
    }


    /**
    * Permet de parser une url
    * @param $url Url à parser
    * @return tableau contenant les paramètres
    **/
    public static function parse($url,$request)
    {
        $url = trim($url,'/');
        if (empty($url)) {
            $url = Router::$routes[0]['url'];
            $auth = Router::$routes[0]['auth'];
        } else {
            $match = false;
            foreach (Router::$routes as $v) {
                if (!$match && preg_match($v['redirreg'], $url, $match)) {
                    $url = $v['origin'];
                    $auth = $v['auth'];
                    foreach ($match as $k => $v) {
                        $url = str_replace(':'.$k.':', $v, $url);
                    }
                    $match = true;
                }
            }
        }

        $params = explode('/',$url);
        if (in_array($params[0], array_keys(self::$prefixes))) {
            $request->prefix = self::$prefixes[$params[0]];
            array_shift($params);
        }
        $request->controller = isset($params[0]) ? $params[0] : Router::$routes[0]['url'];
        $request->action = isset($params[1]) ? $params[1] : self::$default_action;
        $request->authreq = isset($auth) ? $auth : self::$default_auth;
        $request->params = array_slice($params,2);
        return true;
    }


    /**
    * Permet de connecter une url à une action particulière
    **/
    public static function connect($redir, $url, $auth = null)
    {
        $auth = $auth === null ? Router::$default_auth : $auth;
        $r = array();
        $r['params'] = array();
        $r['url'] = $url;
        $r['auth'] = $auth;

        $r['originreg'] = preg_replace('/([a-z0-9]+):([^\/]+)/', '${1}:(?P<${1}>${2})', $url);
        $r['originreg'] = str_replace('/*', '(?P<args>/?.*)', $r['originreg']);
        $r['originreg'] = '/^'.str_replace('/', '\/', $r['originreg']).'$/';
        // MODIF
        $r['origin'] = preg_replace('/([a-z0-9]+):([^\/]+)/', ':${1}:', $url);
        $r['origin'] = str_replace('/*', ':args:', $r['origin']);

        $params = explode('/', $url);

        //  builing list of controllers
        if (!in_array($params[0], self::$controllers)) {
            self::$controllers[] = $params[0];
        }

        foreach($params as $k => $v) {
            if (strpos($v, ':')) {
                $p = explode(':', $v);
                $r['params'][$p[0]] = $p[1];
            }
        }

        $r['redirreg'] = $redir;
        $r['redirreg'] = str_replace('/*', '(?P<args>/.*)', $r['redirreg']);

        foreach ($r['params'] as $k => $v) {
            $r['redirreg'] = str_replace(":$k", "(?P<$k>$v)", $r['redirreg']);
        }

        $r['redirreg'] = '/^'.str_replace('/', '\/', $r['redirreg']).'$/';

        $r['redir'] = preg_replace('/:([a-z0-9]+)/', ':${1}:', $redir);
        $r['redir'] = str_replace('/*', ':args:', $r['redir']);

        self::$routes[] = $r;
    }

    /**
    * Permet de générer une url à partir d'une url originale
    * controller/action(/:param/:param/:param...)
    **/
    public static function url($url = '')
    {
        trim($url,'/');
        foreach (self::$routes as $v) {
            if (preg_match($v['originreg'], $url, $match)) {
                $url = $v['redir'];
                foreach ($match as $k => $w) {
                    $url = str_replace(":$k:", $w, $url);
                }
            }
        }
        foreach (self::$prefixes as $k => $v) {
            if (strpos($url, $v) === 0) {
                $url = preg_replace("/^$v/", $k, $url);
            }
        }
        return BASE_URL.'/'.$url;
    }

    public static function webroot($url)
    {
        trim($url, '/');
        return BASE_URL.'/'.$url;
    }

    /*
    *   nsDefine
    *       define a namespace
    */
    public static function nsDefine($class, $ns)
    {
        self::$nsTable[$class] = $ns;
    }

    /*
    *   nsMatch
    *       retrieve namespace for class
    */
    public static function nsMatch($class)
    {
        $ns = isset(self::$nsTable[$class]) ? self::$nsTable[$class] : self::$default_ns;
        return $ns;
    }

    /*
    *   ctlSearch
    *       is a contrller present in dynamic list
    */
    public static function ctlSearch($ctl)
    {
        if (in_array($ctl, self::$controllers)) {
            return true;
        }

        return false;
    }

}
<?php

use spacelife\core\Router;

//  prefixes
Router::prefix('manager', 'admin');
Router::prefix('m', 'api');

//  routes
//  home
Router::connect('/', 'home', false);
Router::connect('home/*', 'home/*', false);

//  user
Router::connect('user/login/*', 'user/login/*', false);
Router::connect('user/facebooklink/*', 'user/linkFacebook/*', false);
Router::connect('user/facebook/*', 'user/loginFacebook/*', false);
Router::connect('user/register', 'user/register', false);
Router::connect('user/lost', 'user/lost', false);
Router::connect('user/activate/:login/:token', 'user/activate/login:([a-zA-Z0-9_-]*)/token:([a-f0-9]*)', false);
Router::connect('user/resetPassword/:token', 'user/resetPassword/token:([a-f0-9]*)', false);
Router::connect('user', 'user', true);
Router::connect('cache', 'cache', true);

//  namespaces
Router::nsDefine('cache', 'spacelife');
Router::nsDefine('home', 'spacelife');
Router::nsDefine('user', 'spacelife');
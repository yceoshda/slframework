# Routing

The routing is handled by the Router class. It routes requests to the need controller and method and is also able to create public urls from private routes.

The class is based on a routing class provided by Grafikart from http://www.grafikart.fr specifically from his tutorial on how to build a website using MVC. It has been modified to accomodate namespaces and authentication discrimination based on specific routes.

It is using raw php to define routes (although I'm pretty sure one day or another I'll refactor this into a more "friendly" format).

Routes and over runtime configuration are located in the routes.php file in the www/config folder.

## Connect

This method is used to connect a public (web) route to internal (controller) route. The method takes 2 mandatory parameters and 1 optionnal, the public route, the internal route and optionnally the need for authentication or not.

Connections can also include requirements for parameters in their public/internal routes.

```php

//  basic route requiring authentication
Router::connect('public/route', 'internal/route', true);

//  route with parameters (first one only numbers, second one numbers and letters (both caps and regular))
Router::connect('public/route/:param/:other', 'internal/route/param:([0-9]*)/other:([0-9a-zA-Z]*)', true);

```

It is also building a list of routed controllers on the fly (to prevent unnecessary errors because of the autoloader). When processing a request the framework will then use the ctlSearch method to make sure the requested controller does exists in the referential. this allows the framework to redirect to an error page in case the controller does not exist (instead of a nasty error from the autoloader ;-)).

## Prefixes

Routes can also include a prefix, as for routes, it takes 2 parameters: the public prefix and the internal prefix.

Prefixes are then used when finding the controller method to be used to serve the request. For example an internal prefix 'admin' as in admin/user/edit will execute the method admin_edit from the User controller. If you use the router to generate urls (through the url method), public urls prefixes will be translated accordingly, meaning you can change a public prefix for all adresses by editing just the prefix line!

```php

//  prefixing admin (internal) by manager (public)
Router::prefix('manager', 'admin');

```

## Namespaces reference

To handle namespaces correctly in the scope of a modern application, you have to build a namespace correspondance list. To do so, use the 'nsDefine' method, it takes two parameters, the name of the class and it's corresponding namespace.

```php

//  defining namespace for user
Router::nsDefine('user', 'spacelife');

```

When processing a request, the framework will use the nsMatch method to retrieve the namespace associated with the desired controller

## Urls

The 'url' method is used to generate public URLs from an internal one using defined routes. This is mainly (if not only) used in views.

```php

//  create a public version of the url
Router::url('admin/user/list');

```

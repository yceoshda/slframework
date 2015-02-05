# Views

Views are what the visitor will see on his browser.

There are two types of views, views and layouts. Views are used to display all sort of information and data. Layouts are used for the final render of a page. Basically you can use one or more views to be included in the final layout. But in the end they work the same way so.

SL Framework uses a custom templating engine. Like the rest of the framework, this templating engine is very basic, no twig, jade or high end things here, just basic HMTL and PHP. A view is a basic .php file containing mainly HTML with a few echoes (as per convention, conditionnals and loops are also permitted!).

There should not be any real "code" in a view, keep it simple, keep it clear. Code belongs to controllers (as data access belongs to models).

## Engine

The engine works as many others do, instanciate an object with the wanted template, feed it your variables and call the render method. Most of the time file paths will be automatically discovered and languages automatically loaded.

The engine automatically loads translations and configuration. This is why you should always use the controller's 'template' method. To maintain structural integrity the fact that 'views/' is the basedir is hardcoded (but if you feel like it the changes aren't that difficult to implement!).

A controller property is used to autodetect paths: 'template_dir' is used to locate files, this path is relative to 'views/'.

Templates can be localized (using _fr for French, or _en for English ...), but you don't need to specify them when calling a template, the engine will automatically try to load the correct localized version (if not available, the default language localized version and finally the "no language" version). This behavior is applied to all localized elements: try current language, try default language, try no language. Of course if none of these versions are found the engine will throw and SLException!

The file extension (.php) should always be ommitted as it may interfere with the autodetect mecanism.

## Injection

You can load a view (or template) from a controller with the 'loadTemplate' method (or the shortcut 'template'). Although a view can be loaded manually, using the method will automatically pass it the correct parameters and auto-detect a few things such as paths, languages ...

Example in a controller:

```php

//  create the object
$tpl = $this->template('myFile');

//  feed it variables
$tpl->set('var_1', $var1);
$tpl->set('var_2', $var2);
$tpl->set('var_3', $var3);

//  render
$html = $tpl->render();

```

## Layouts

This specific kind of views is handled automatically by the controller. You don't need to worry about it, but you can act on it with several tools.

The layout to be used is defined by a controller property shockingly named 'layout'! ('admin_layout' for admin pages). This property has a default value but it can be overloaded. In API mode, this property will be automatically changed to a more stripped down version.

There are a few required variables for layouts for css, js, title and content.

## Helpers

The framework provides a few helpers to be used in views.

### HTML

The 'HTML' helper allows the injection of custom css and js through the static methods 'css' and 'js'. The injected files are supposed to be in the public ('web') folder in their respective subfolders ('web/js' and 'web/css'). And of course file extensions can be ommitted.

### Form

The 'Form' helper is designed to be fully integrated with models and translations in order to provide an easy way of creating forms and handling errors. The form object has to be created in the controller with the 'createForm' method passing it data, errors and translation scope to be used. Then you have to feed the object to the view with the 'set' method. it is also using Twitter Bootstrap css classes to build the html code, so you don't have to worry about it in the view.

The usage is then really simple.

The following example is a simplified version of the register page.

Controller:

```php

//  template stuff
$tpl = $this->template('register');

//  create the form object
$form = $this->createForm($data, $errors, 'user');

//  feed it to the view
$tpl->set('form', $form);

//  do everything else you need ...

```

View:

```php

$form->start(['url' => 'user/register']);

$form->email('email', ['required']);

$form->text('login', ['required']);

$form->password('password', ['required']);

$form->radio('lang', ['fr', 'en']);

$form->button('register', ['submit']);
$form->button('cancel', ['url' => 'home/index']);

$form->close();

```

Of course the translation file must include a 'fields' section to handle fields translations (including potential errors) and a 'btn' section for buttons.

I strongly advise you to look at what's being done on the user_xx.json file for translations. And of course reviewing the whole 'User' controller / 'Users' model can give you great many examples.

### Router

You should always use the router to generate URLs inside a view. Don't forget to add the namespace at the top.

```php

use spacelife\core\Router;

//  things ...

Router::url('my/url');

```

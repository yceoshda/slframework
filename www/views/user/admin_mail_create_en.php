<?php use spacelife\core\Router; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    </head>
    <body>
        <h2>Hello <?=$user->first_name; ?> <?=$user->last_name; ?></h2>
        <p>An account has just been created for you on <a href="<?=$_config->siteUrl; ?>"><?=$_config->title; ?></a></p>
        <p>Your login is: <?=$user->login; ?>. To activate your account, you need to create your password.</p>
        <p>Follow (or copy) the link to finalize your account activation: <a href="<?=$_config->siteUrl; ?><?=Router::url('user/activate/'.$user->login.'/'.$activation_token.'?lang='.$user->lang); ?>"><?=$_config->siteUrl; ?><?=Router::url('user/activate/'.$user->login.'/'.$activation_token); ?></a></p>
    </body>
</html>
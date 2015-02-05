<?php use spacelife\core\Router; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    </head>
    <body>
        <h2>Bonjour <?=$user->first_name; ?> <?=$user->last_name; ?></h2>
        <p>Un compte vient d'etre cree pour vous sur le site <a href="<?=$_config->siteUrl; ?>"><?=$_config->title; ?></a></p>
        <p>Votre identifiant pour vous connecter est: <?=$user->login; ?>. Pour creer un mot de passe vous devez activer votre compte.</p>
        <p>Pour activer votre compte, cliquez sur le lien suivant: <a href="<?=$_config->siteUrl; ?><?=Router::url('user/activate/'.$user->login.'/'.$activation_token.'?lang='.$user->lang); ?>"><?=$_config->siteUrl; ?><?=Router::url('user/activate/'.$user->login.'/'.$activation_token); ?></a></p>
    </body>
</html>
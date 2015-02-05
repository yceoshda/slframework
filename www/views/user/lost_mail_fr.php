<?php use spacelife\core\Router; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    </head>
    <body>
        <h2>Recuperation SpaceLife</h2>
        <p>
            <?=$user->login; ?> vous avez recemment demande la recuperation du mot de passe de votre compte SpaceLife. Malheureusement, pour des raisons de securites, les mots de passe sont perdus dans le vide intersideral de l'espace profond plutot que stockes dans une base de donnees ! <br>
            Mais ne vous inquietez pas, le commandement central de SpaceLife est en mesure de vous fournir un lien a usage unique vous permettant de reinitialiser votre mot de passe. <br>
            Merci de cliquer sur le lien ci-dessous pour re-initialiser votre mot de passe. Attention, le lien n'est valable qu'une (1) heure.
        </p>
        <p><a href="<?=$siteUrl; ?><?=Router::url('user/resetPassword'); ?>/<?=$lost_token; ?>"><?=$siteUrl; ?><?=Router::url('user/resetPassword'); ?>/<?=$lost_token; ?></a></p>
        <p>Si vous n'avez pas demande la re-initialisation de votre mot de passe, vous pouvez simplement ignorer cet e-mail. Mais, pour des raisons de securite, le commandement central de SpaceLife vous recommande de vous assurer de la securisation de vos comptes Internet (mails, facebook ...)</p>
    </body>
</html>
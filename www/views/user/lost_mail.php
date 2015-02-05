<?php use spacelife\core\Router; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    </head>
    <body>
        <h2>SpaceLife Recovery</h2>
        <p>
            <?=$user->login; ?> you have recently asked to recover your password for your SpaceLife account. Unfortunately for security reasons passwords are lost in the nether of deep space instead of stored in a database! <br>
            But don't worry, SpaceLife central command is able to give you a one time link to reset your password. <br>
            Please click the link below to reset your password. Be careful this link will remain valid only for one (1) hour.
        </p>
        <p><a href="<?=$siteUrl; ?><?=Router::url('user/resetPassword'); ?>/<?=$lost_token; ?>"><?=$siteUrl; ?><?=Router::url('user/resetPassword'); ?>/<?=$lost_token; ?></a></p>
        <p>If you did not asked for a password reset link, you can simply ignore this email. But, for security reasons, SpaceLife central command strongly advise you to carefully check your Internet accounts' security (e-mails, Facebook ...)</p>
    </body>
</html>
<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->registred->title; ?> <?=$user->login; ?></h1>
<h3><?=$_translate->user->registred->subtitle; ?></h3>
<!-- <p>Verifiez vos mails pour recuperer votre code d'activation.</p> -->
<p>Vous pouvez vous <a href="<?=Router::url('user/login/'.$user->login); ?>" class="btn btn-primary">connecter</a> à votre empire</p>
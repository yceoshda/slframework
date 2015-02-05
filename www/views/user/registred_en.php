<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->registred->title; ?> <?=$user->login; ?></h1>
<h3><?=$_translate->user->registred->subtitle; ?></h3>
<!-- <p>Please check your mails for your activation code.</p> -->
<p>You can <a href="<?=Router::url('user/login/'.$user->login); ?>" class="btn btn-primary">connect</a> to your empire</p>
<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->activate->title; ?></h1>
<h3><?=$user->login; ?></h3>
<br><br>
<p><?=$_translate->user->activate->welcome; ?></p>
<br><br>
<div class="container">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <?=$form->start(['url' => 'user/activate/'.$user->login.'/'.$user->password, 'disable_id']); ?>

            <?=$form->hidden('token', $user->password); ?>
            <?=$form->hidden('id', $user->id); ?>

            <?=$form->password('password'); ?>

            <?=$form->password('password2'); ?>

            <div class="form-group">
                <div class="col-sm-2"></div>
                <div class="col-sm-8">
                    <?=$form->button('activate', ['submit']); ?>
                    <?=$form->button('cancel', ['url' => 'home/index']); ?>
                </div>
                <div class="col-sm-2"></div>
            </div>
        <?=$form->close(); ?>
    </div>
    <div class="col-sm-2"></div>
</div>
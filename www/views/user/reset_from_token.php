<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->resetPassword->title; ?></h1>
<h3><?=$login; ?></h3>
<br><br>
<div class="container">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">

        <?=$form->start(['url' => 'user/resetPassword/'.$token, 'disable_url_id']); ?>

            <?=$form->hidden('token'); ?>

            <?=$form->password('password'); ?>

            <?=$form->password('password2'); ?>

            <div class="form-group">
                <div class="col-sm-2"></div>
                <div class="col-sm-8">
                    <?=$form->button('save', ['submit']); ?>
                    <?=$form->button('cancel', ['url' => 'home/index']); ?>
                </div>
                <div class="col-sm-2"></div>
            </div>

        <?=$form->close(); ?>

    </div>
    <div class="col-sm-2"></div>
</div>
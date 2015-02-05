<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->editprofile->title; ?></h1>
<h3><?=$login; ?></h3>
<br><br>
<div class="container">
    <br><br>
    <div class="container">
        <div class="col-sm-12">

            <?=$form->start(['url' => 'user/editProfile']); ?>

                <?=$form->text('login', ['required']); ?>

                <?=$form->radio('gender', ['mr', 'mrs', 'oth']); ?>

                <?=$form->text('last_name'); ?>

                <?=$form->text('first_name'); ?>

                <?=$form->radio('name_visible', ['public', 'private']); ?>

                <?=$form->email('email', ['required', 'addon' => '@']); ?>

                <?=$form->radio('lang', $_config->langs); ?>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <?=$form->button('save', ['submit']); ?>

                        <?=$form->button('cancel', ['url' => 'user/profile']); ?>
                    </div>
                </div>

            <?=$form->close(); ?>
        </div>
    </div>
</div>
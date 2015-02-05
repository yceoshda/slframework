<?php use spacelife\core\Router; ?>
<h1><?=$_translate->admin_user->create->title; ?></h1>
<h3><?=$_translate->admin_user->create->subtitle; ?></h3>
<div class="container">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <?=$form->start(['url' => 'admin/user/create']); ?>

            <?=$form->text('login', ['required' => true]); ?>

            <?=$form->email('email', ['required' => true]); ?>

            <?=$form->radio('gender', ['mr', 'mrs', 'oth']); ?>

            <?=$form->text('first_name'); ?>

            <?=$form->text('last_name'); ?>

            <?=$form->checkbox('is_admin'); ?>

            <?=$form->radio('lang', $_config->langs); ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?=$form->button('create', ['submit']); ?>
                    <?=$form->button('cancel', ['url' => 'admin/user/list']); ?>
                </div>
            </div>

        <?=$form->close(); ?>
    </div>
    <div class="col-sm-1"></div>
</div>
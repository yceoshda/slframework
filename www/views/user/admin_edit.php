<?php use spacelife\core\Router; ?>
<h1><?=$_translate->admin_user->edit->title; ?></h1>
<h3><?=$_translate->admin_user->edit->subtitle; ?></h3>
<div class="container">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <?=$form->start(['url' => 'admin/user/edit']); ?>

            <?=$form->text('login', ['required']); ?>

            <?=$form->text('email', ['required']); ?>

            <?=$form->radio('gender', ['mr', 'mrs', 'oth']); ?>

            <?=$form->text('first_name'); ?>

            <?=$form->text('last_name'); ?>

            <?=$form->radio('name_visible', ['private', 'public']); ?>

            <?=$form->radio('lang', $_config->langs); ?>

            <?=$form->checkbox('is_admin'); ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?=$form->button('edit', ['submit']); ?>
                    <?=$form->button('cancel', ['url' => 'admin/user/list']); ?>
                </div>
            </div>

        </form>
    </div>
    <div class="col-sm-1"></div>
</div>
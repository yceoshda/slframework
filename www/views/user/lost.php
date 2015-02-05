<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->lost->title; ?></h1>
<h3><?=$_translate->user->lost->subtitle; ?></h3>
<br><br>
<div class="container">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">

        <?=$form->start(['url' => 'user/lost', 'disable_id']); ?>

            <?=$form->email('email', ['required', 'addon' => '@']); ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?=$form->button('send', ['submit']); ?>
                    <?=$form->button('cancel', ['url' => 'home/index']); ?>
                </div>
            </div>
        <?=$form->close(); ?>
    </div>
</div>
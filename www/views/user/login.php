<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->login->title; ?></h1>
<h3><?=$_translate->user->login->subtitle; ?></h3>
<br><br>
<?=(isset($message) ? $message : ''); ?>
<div class="container">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">

        <?=$form->start(['url' => 'user/login']); ?>

            <?=$form->text('login', ['placeholder' => 'jsmith42']); ?>

            <?=$form->password('password'); ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?=$form->button('connect', ['submit']); ?>

                    <?=$form->button('cancel', ['url' => 'home/index']); ?>

                    <?php if ($resetEnabled === true): ?>
                        <?=$form->button('forgot', ['url' => 'user/lost', 'class' => 'warning']); ?>
                    <?php endif ?>

                    <?php if ($_config->facebook->enable === true): ?>
                        <?=$form->button('facebook', ['url' => 'user/loginFacebook', 'class' => 'info']); ?>
                    <?php endif ?>
                </div>
            </div>

        <?=$form->close(); ?>

    </div>
    <div class="col-sm-2"></div>
</div>
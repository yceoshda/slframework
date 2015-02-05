<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->register->title; ?></h1>
<h3><?=$_translate->user->register->subtitle; ?></h3>
<br><br>
<?=(isset($message_failure) ? "$message_failure" : ''); ?>
<div class="container">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">
        <?=$form->start(['url' => 'user/register', 'disable_csrf']); ?>

            <?=$form->text('login', ['required']); ?>

            <?=$form->email('email', ['required']); ?>

            <?=$form->password('password', ['required']); ?>

            <?=$form->radio('lang', $_config->langs); ?>

            <div class="form-group slhuman">
                <label for="slhuman" class="col-sm-2 control-label">Si vous etes humain, ne remplissez pas ce champ</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="slhuman" name="slhuman" placeholder="ne pas remplir">
                    <span class="help-block">Non, vraiment si par hasard vous voyez ce champ, ne le remplissez pas !</span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?=$form->button('register', ['submit']); ?>
                    <?=$form->button('cancel', ['url' => 'home/index']); ?>
                    <?php if ($_config->facebook->enable === true): ?>
                        <?=$form->button('facebook', ['url' => 'user/loginFacebook', 'class' => 'info']); ?>
                    <?php endif ?>
                </div>
            </div>
        <?=$form->close(); ?>
    </div>
    <div class="col-sm-2"></div>
</div>
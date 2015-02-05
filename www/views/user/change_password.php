<?php use spacelife\core\Router; ?>
<h1><?=$_translate->user->changePassword->title; ?></h1>
<h3><?=$login; ?></h3>
<br><br>
<?=(isset($message_failure) ? "$message_failure" : ''); ?>
<div class="container">
    <div class="col-sm-2"></div>
    <div class="col-sm-8">

        <?=$form->start(['url' => 'user/changePassword']); ?>

            <?=$form->password('current_password'); ?>

            <hr>

            <?=$form->password('password'); ?>

            <?=$form->password('password2'); ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <?=$form->button('save', ['submit']); ?>

                    <?=$form->button('cancel', ['url' => 'user/profile']); ?>
                </div>
            </div>
        </form>
    </div>
    <div class="col-sm-2"></div>
</div>
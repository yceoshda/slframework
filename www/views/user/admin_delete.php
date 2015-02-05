<?php use spacelife\core\Router; ?>
<h1><?=$_translate->admin_user->delete->title; ?></h1>
<h3><?=$_translate->admin_user->delete->subtitle; ?></h3>
<div class="container">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <h4><?=$_translate->admin_user->delete->confirm; ?> <?=$user->login; ?></h4>
        <form action="<?=Router::url('admin/user/delete/'.$user->id); ?>" method="post" role="form">
            <input type="hidden" name="csrf" value="<?=$csrf; ?>">
            <input type="hidden" name="id" value="<?=$user->id; ?>">
            <button class="btn btn-danger" type="submit"><?=$_translate->admin_user->btn->delete; ?></button>
            <a href="<?=Router::url('admin/user/list'); ?>" class="btn btn-default"><?=$_translate->admin_user->btn->cancel; ?></a>
        </form>
    </div>
    <div class="col-sm-1"></div>
</div>
<?php use spacelife\core\Router; ?>
<h1><?=$_translate->admin_user->view->title; ?></h1>
<h3><?=$_translate->admin_user->view->subtitle; ?></h3>
<br>
<?php if ($user === false): ?>
    <p><?=$_translate->admin_user->view->nouser; ?></p>
<?php else: ?>
    <div class="container">
        <ul class="list-group">
            <li class="list-group-item"><h4><?=$_translate->admin_user->fields->login->label; ?>:</h4> <?=$user->login; ?></li>
            <li class="list-group-item"><h4><?=$_translate->admin_user->fields->email->label; ?>:</h4> <?=$user->email; ?></li>
            <li class="list-group-item"><h4><?=$_translate->admin_user->fields->gender->label; ?></h4> <?=$_translate->admin_user->fields->gender->{$user->gender}->full; ?></li>
            <li class="list-group-item">
                <h4><?=$_translate->admin_user->fields->first_name->label; ?>:</h4>
                <?=$user->first_name; ?>
            </li>
            <li class="list-group-item">
                <h4><?=$_translate->admin_user->fields->last_name->label; ?>:</h4>
                <?=$user->last_name; ?>
            </li>
            <li class="list-group-item">
                <h4><?=$_translate->admin_user->fields->name_visible->label; ?></h4>
                <span class="label label-<?=($user->name_visible == 'private' ? 'danger' : 'success'); ?>"><?=$_translate->admin_user->tags->name_visible->{$user->name_visible}; ?></span>
            </li>
            <li class="list-group-item"><h4><?=$_translate->admin_user->fields->created->label; ?>:</h4> <?=$user->created; ?></li>
            <li class="list-group-item"><h4><?=$_translate->admin_user->fields->updated->label; ?>:</h4> <?=$user->updated; ?></li>
            <li class="list-group-item"><h4><?=$_translate->admin_user->fields->last_login->label; ?>:</h4> <?=$user->last_login; ?></li>
            <li class="list-group-item">
                <h4><?=$_translate->admin_user->fields->last_fail->label; ?> (<?=$_translate->admin_user->fields->fail_count->label; ?>): <span class="label label-danger"><?=($user->fail_count >= $_config->passwordRules->maxRetry ? $_translate->admin_user->tags->locked: ''); ?></span></h4>
                <?=$user->last_fail; ?> (<?=$user->fail_count; ?>)
            </li>
        </ul>
        <a href="<?=Router::url('admin/user/list'); ?>" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <?=$_translate->admin_user->btn->return; ?></a>
        <a href="<?=Router::url('admin/user/edit/'.$user->id); ?>" class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> <?=$_translate->admin_user->btn->edit; ?></a>
        <?php if ($user->fail_count >= $_config->passwordRules->maxRetry): ?>
            <a href="<?=Router::url('admin/user/unlock/'.$user->id); ?>" class="btn btn-warning"><?=$_translate->admin_user->btn->unlock; ?></a>
        <?php endif ?>
        <a href="<?=Router::url('admin/user/delete/'.$user->id); ?>" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> <?=$_translate->admin_user->btn->delete; ?></a>
    </div>
<?php endif ?>

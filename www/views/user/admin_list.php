<?php use spacelife\core\Router; ?>
<h1><?=$_translate->admin_user->list->title; ?></h1>
<h3><?=$_translate->admin_user->list->subtitle; ?></h3>
<br>
<?=$form->button('create', ['link', 'class' => 'primary', 'url' => 'admin/user/create']); ?>
<br>
<?php if ($users === false): ?>
    <p><?=$_translate->admin_user->list->nouser; ?></p>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <td><?=$_translate->admin_user->list->thead->login; ?></td>
                <td><?=$_translate->admin_user->list->thead->tags; ?></td>
                <td><?=$_translate->admin_user->list->thead->actions; ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?=$user->login; ?></td>
                    <td>
                        <span class="label label-primary"><?=($user->is_admin == 1 ? $_translate->admin_user->tags->admin : ''); ?></span>
                        <span class="label label-danger"><?=($user->fail_count >= 5 ? $_translate->admin_user->tags->locked : ''); ?></span>
                    </td>
                    <td>
                        <?=$form->button('edit', ['url' => 'admin/user/edit/'.$user->id, 'class' => 'primary']); ?>

                        <?=$form->button('delete', ['url' => 'admin/user/delete/'.$user->id, 'class' => 'danger']); ?>

                        <?=$form->button('view', ['url' => 'admin/user/view/'.$user->id, 'class' => 'default']); ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
    <?=$form->button('create', ['link', 'class' => 'primary', 'url' => 'admin/user/create']); ?>
<?php endif ?>

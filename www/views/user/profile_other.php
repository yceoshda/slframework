<?php use spacelife\core\Router; ?>
<h1><?=$profile->title; ?></h1>
<h3><?=$user->login; ?></h3>
<br><br>
<div class="container">
    <br><br>
    <table class="table table-striped table-hover">
        <tbody>
            <tr>
                <td><?=$profile->login; ?></td>
                <td><?=$user->login; ?></td>
            </tr>
                <tr>
                    <td><?=$profile->name; ?></td>
                    <td>
                    <?php if ($user->name_visible == 'public'): ?>
                        <?=($user->gender == '' ? '' : $profile->gender->{$user->gender}->abv); ?>
                        <?=($user->first_name != '' ? $user->first_name : ''); ?>
                        <?=($user->last_name != '' ? $user->last_name : ''); ?>
                    <?php else: ?>
                        <span class="label label-danger"><?=$profile->name_visible->private; ?></span>
                    <?php endif ?>
                    </td>
                </tr>
            <tr>
                <td><?=$profile->created; ?></td>
                <td><?=date($dateFormat, $user->created_ts); ?></td>
            </tr>
            <tr>
                <td><?=$profile->lang->label; ?></td>
                <td><?=$profile->lang->{$user->lang}; ?></td>
            </tr>
        </tbody>
    </table>
</div>
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
                <td>
                    <?=$user->login; ?>
                </td>
            </tr>
            <tr>
                <td><?=$profile->email; ?></td>
                <td><?=$user->email; ?></td>
            </tr>
            <tr>
                <td><?=$profile->name; ?></td>
                <td>
                    <?=($user->gender == '' ? '' : $profile->gender->{$user->gender}->abv); ?>
                    <?=($user->first_name != '' ? $user->first_name : ''); ?>
                    <?=($user->last_name != '' ? $user->last_name : ''); ?>
                    <span class="label label-<?=($user->name_visible == 'public' ? 'success' : 'danger'); ?>"><?=$profile->name_visible->{$user->name_visible}; ?></span>
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
    <a href="<?=Router::url('user/changePassword'); ?>" class="btn btn-primary"><?=$profile->changePassword; ?></a>
    <a href="<?=Router::url('user/editProfile'); ?>" class="btn btn-info"><?=$profile->editProfile; ?></a>
    <?php if (!$user->facebook_id && $_config->facebook->enable): ?>
        <a href="<?=Router::url('user/linkFacebook'); ?>" class="btn btn-info"><?=$profile->linkFacebook; ?></a>
    <?php endif ?>
</div>
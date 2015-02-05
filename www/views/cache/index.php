<?php use spacelife\core\Router; ?>
<h1><?=$_translate->cache_admin->index->title; ?></h1>
<h3><?=$_translate->cache_admin->index->subtitle; ?></h3>
<br><br>
<?php if ($files === false): ?>
    <?=$_translate->cache_admin->index->nocache; ?>

<?php else: ?>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <td><?=$_translate->cache_admin->index->thead->filename; ?></td>
                <td><?=$_translate->cache_admin->index->thead->ttl; ?></td>
                <td><?=$_translate->cache_admin->index->thead->actions; ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td><?=$file['filename'].'.'.$file['extension']; ?></td>
                    <td><span class="text-<?=$file['duration']['status']; ?>"><?=$file['duration']['hms'] ?></span></td>
                    <td><?=$form->button('delete', ['url' => 'admin/cache/delete/'.$file['filename'].'.'.$file['extension'], 'class' => 'danger']); ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

<?php endif ?>
<br>
<p>
    <?=$form->button('refresh', ['url' => 'admin/cache/index', 'class' => 'primary']); ?>
    <?=$form->button('deleteall', ['url' => 'admin/cache/deleteall', 'class' => 'danger']); ?>
</p>
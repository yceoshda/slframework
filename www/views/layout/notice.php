(function ($){
    <?php foreach ($notices as $notice): ?>
        new sl_utils.triggerNoticeEvent({"type": "<?=$notice['type']; ?>", "message": "<?=$notice['message']; ?>", "timeout": <?=$notice['timeout'] * 1000; ?>});
    <?php endforeach ?>
} (sl_utils));
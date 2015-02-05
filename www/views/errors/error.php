<?php use spacelife\core\Router ?>
<h1>Error</h1>
<p>Something went wrong while processing your request :-(</p>
<p><?=$message; ?></p>
<a href="<?=Router::url('home/index'); ?>">Back home</a>
<?php if ($_config->$debug_trace): ?>
    <pre>
        <?=print_r(debug_backtrace()); ?>
    </pre>
<?php endif ?>
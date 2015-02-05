<?php use spacelife\core\Router; ?>
<h1>404 Error</h1>
<h3>A blackhole has swallowed the page you are looking for :-(</h3>
<p><?=$request; ?></p>
<p><?=$message; ?></p>
<a href="<?=Router::url('home'); ?>">Back home</a>
<?php if ($_config->$debug_trace): ?>
    <pre>
        <?=print_r(debug_backtrace()); ?>
    </pre>
<?php endif ?>
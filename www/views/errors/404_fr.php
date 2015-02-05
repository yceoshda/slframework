<?php use spacelife\core\Router; ?>
<h1>Erreur 404</h1>
<h3>Un trou noir a avalé la page que vous cherchiez :-(</h3>
<p><?=$request; ?></p>
<p><?=$message; ?></p>
<a href="<?=Router::url('home'); ?>">Accueil</a>
<?php if ($_config->debug_trace): ?>
    <pre>
        <?=print_r(debug_backtrace()); ?>
    </pre>
<?php endif ?>
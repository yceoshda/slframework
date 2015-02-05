<?php use spacelife\core\Router; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$_lang; ?>" lang="<?=$_lang; ?>">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?=$title; ?></title>
    <link rel="stylesheet" href="<?=Router::webroot('css/app.css'); ?>" type="text/css">
    <!--[if IE]>
        <link href="<?=Router::webroot('css/ie.css'); ?>" media="screen, projection" rel="stylesheet" type="text/css" />
    <![endif]-->
    <?=$css_inject; ?>

    </head>
    <body>
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only"><?=$_translate->layout->menu->toggle_navigation; ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?=Router::url('home/index'); ?>"><?=$title; ?></a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="<?=Router::url('home/index'); ?>"><?=$_translate->layout->menu->top->home; ?></a></li>
                    <li><a href="<?=Router::url('home/about'); ?>"><?=$_translate->layout->menu->top->about; ?></a></li>
                    <li><a href="<?=Router::url('home/contact'); ?>"><?=$_translate->layout->menu->top->contact; ?></a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php foreach ($_config->langs as $lang): ?>
                        <li <?=($lang == $_lang ? 'class="active"' : ''); ?>><a href="?lang=<?=$lang; ?>"><?=$_translate->layout->menu->top->lang->$lang; ?></a></li>
                    <?php endforeach ?>
                    <?php if (!isset($_SESSION['login'])): ?>
                        <li><a href="<?=Router::url('user/login'); ?>"><?=$_translate->layout->menu->top->login; ?></a></li>
                        <li><a href="<?=Router::url('user/register'); ?>"><?=$_translate->layout->menu->top->register; ?></a></li>
                    <?php else: ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?=$session->login; ?> <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="<?=Router::url('user/profile'); ?>"><?=$_translate->layout->menu->top->user->profile; ?></a></li>
                                <?php if ($session->is_admin == 1): ?>
                                    <li><a href="<?=Router::url('admin'); ?>"><?=$_translate->layout->menu->top->user->admin; ?></a></li>
                                <?php endif ?>
                                <li><a href="<?=Router::url('user/logout'); ?>"><?=$_translate->layout->menu->top->user->logout; ?></a></li>
                            </ul>
                        </li>
                    <?php endif ?>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>

    <div class="container-fluid">
        <div class="col-sm-2"></div>
        <div class="col-sm-8"></div>
        <div class="col-sm-2"><div id="notif-area" class="sl-notif-area"></div></div>
    </div>

    <div class="container-fluid">
        <div class="col-sm-1"></div>
        <div class="col-sm-10">
            <?=$content; ?>
        </div>
        <div class="col-sm-1"></div>
    </div>

    <br><br>

    </body>
    <script type="text/javascript" src="<?=Router::webroot('js/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?=Router::webroot('js/bootstrap.min.js'); ?>"></script>
    <script type="text/javascript" src="<?=Router::webroot('js/sl_utils.js'); ?>"></script>
    <?=$js_inject; ?>
    <?=$googleanalytics; ?>
</html>
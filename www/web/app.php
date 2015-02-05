<?php

$start_time = microtime(true);
ob_start();

define('WEBROOT', dirname(__FILE__));
define('ROOT', dirname(WEBROOT));
define('DS', DIRECTORY_SEPARATOR);
define('BASE_URL', '');

//  composer autoloader
require_once ROOT.DS."vendor/autoload.php";
require_once ROOT.DS."config/routes.php";
require_once ROOT.DS."config/debug.php";

//  config
$config = new spacelife\tools\Config();

//  application bootstrap
new spacelife\core\Dispatch($config);

ob_end_flush();

if ($config->perf_debug === true) {
    echo '<div align="right" style="color:#555;">Exec-time: '.(round(microtime(true) - $start_time, 5) * 1000).'ms | Memory used: '.round(memory_get_peak_usage() / 1024, 0).' ko</div>';
}


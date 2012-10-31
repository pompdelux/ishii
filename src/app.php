<?php

use Ishii\Application;

function _log($data, $back = 0) {
    $bt = debug_backtrace();
    $line = $bt[$back]['line'];
    $file = str_replace(realpath(__DIR__ . '/../../'), '~', $bt[$back]['file']);
    error_log($file.' +'.$line.' :: '.print_r($data, 1));
}

$app = new Application(array('debug' => true));
$app['debug'] = true;

$app->mount('/gallery', new Ishii\Apps\Gallery\Controller());

return $app;

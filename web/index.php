<?php

require_once __DIR__.'/../vendor/autoload.php';

$loader = new \Composer\Autoload\ClassLoader();

date_default_timezone_set('Europe/Copenhagen');

$loader->add('Ishii', __DIR__.'/../src');
$loader->add('Tobiassjosten\Silex\Provider', __DIR__.'/../vendor/tobiassjosten/facebook-service-provider/lib');

$loader->register();

$app = require __DIR__.'/../src/app.php';
$app->run();

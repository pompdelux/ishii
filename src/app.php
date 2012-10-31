<?php

use Ishii\Application;
use Imagine\Filter;

function _log($data, $back = 0) {
    $bt = debug_backtrace();
    $line = $bt[$back]['line'];
    $file = str_replace(realpath(__DIR__ . '/../../'), '~', $bt[$back]['file']);
    error_log($file.' +'.$line.' :: '.print_r($data, 1));
}

$app = new Application(array('debug' => true));
$app['debug'] = true;

$app->before( function() use ( $app ) {
    $flash = $app[ 'session' ]->get( 'flash' );
    $app[ 'session' ]->set( 'flash', null );

    if ( !empty( $flash ) )
    {
        $app[ 'twig' ]->addGlobal( 'flash', $flash );
    }
});

$app->mount('/gallery', new Ishii\Apps\Gallery\Controller());

$app->get('/image/{file}/{width}', function($file, $width) use ($app){
	$image = $app['imagine']->open('uploads/'.$file);
	$size = $image->getSize();
    $transformation = new Filter\Transformation();
    $transformation->thumbnail($size->widen($width));
    $image = $transformation->apply($image);

    $format = pathinfo($file, PATHINFO_EXTENSION);

    $response = new Symfony\Component\HttpFoundation\Response();
    $response->headers->set('Content-type', 'image/'.$format);
    $response->setContent($image->get($format));

    return $response;
})->bind('image')->value('width', 600);

return $app;

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

/**
 * Image getter. Returns an resized image. Image keeps ratio aspects
 *
 * @param  string      	$file  	the image name
 * @param  int      	$width  the width of the wanted image
 * @return Response             Response object
 */
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

/**
 * Image getter. Returns a cropped version of the image. The thumbnail has a fixed height/width of @param $dimension
 *
 * @param  string       $file       the image name
 * @param  int          $dimnesion  the dimension of the wanted image
 * @return Response                 Response object
 */
$app->get('/thumb/{file}/{dimension}', function($file, $dimension) use ($app){
    $image = $app['imagine']->open('uploads/'.$file);
    $size = $image->getSize();
    $transformation = new Filter\Transformation();
    $transformation->thumbnail(new Imagine\Image\Box($dimension, $dimension), Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);
    $image = $transformation->apply($image);

    $format = pathinfo($file, PATHINFO_EXTENSION);

    $response = new Symfony\Component\HttpFoundation\Response();
    $response->headers->set('Content-type', 'image/'.$format);
    $response->setContent($image->get($format));

    return $response;
})->bind('thumb')->value('dimension', 200);

return $app;

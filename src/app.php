<?php

use Ishii\Application;
use Imagine\Filter;
use Symfony\Component\HttpFoundation\Request;

function _log($data, $back = 0) {
    $bt = debug_backtrace();
    $line = $bt[$back]['line'];
    $file = str_replace(realpath(__DIR__ . '/../../'), '~', $bt[$back]['file']);
    error_log($file.' +'.$line.' :: '.print_r($data, 1));
}

$app = new Application(array('debug' => true));
$app['debug'] = true;

$app->before( function() use ( $app ) {
    $flash = $app['session']->get('flash');
    $app['session']->set('flash', null);

    if (!empty($flash)) {
        $app['twig']->addGlobal('flash', (array) $flash);
    }
});

$app->mount('/gallery', new Ishii\Apps\Gallery\Controller());
$app->mount('/admin', new Ishii\Apps\Admin\Controller());

$app->error(function (\Exception $e, $code) use ($app) {

    // commented for testing purposes
    /*if ($app['debug']) {
        return;
    }*/

    $app['monolog']->addError($e);
    $app['monolog']->addError(print_r(debug_backtrace($e)));
    if ($code == 404) {
        return new Symfony\Component\HttpFoundation\Response( $app['twig']->render('404.twig', array('message' => $e->getMessage())), 404);
    }

    return new Symfony\Component\HttpFoundation\Response('We are sorry, but something went terribly wrong.', $code);

});


/**
 * Image getter. Returns an resized image. Image keeps ratio aspects
 * Uses a cached version if it exists, otherwise it creates one and returns that.
 *
 * @param  string      	$file  	the image name
 * @param  int      	$width  the width of the wanted image
 * @return Response             Response object
 */
$app->get('/image/{file}/{width}', function($file, $width) use ($app) {
    try {
       $image = $app['imagine']->open(__DIR__.'/..'.$app['config']['upload_path'].'/cache/'.$width.'-'.$file);
    } catch(Imagine\Exception\Exception $e) {
        _log($e->getMessage());
        try {
            if(!is_dir(__DIR__.'/..'.$app['config']['upload_path'].'/cache/')) {
                mkdir(__DIR__.'/..'.$app['config']['upload_path'].'/cache/', 0777);
            }

            $image = $app['imagine']->open(__DIR__.'/..'.$app['config']['upload_path'].'/'.$file);
            $size = $image->getSize();
            $transformation = new Filter\Transformation();
            $transformation->thumbnail($size->widen($width));
            $image = $transformation
                ->apply($image)
                ->save(__DIR__.'/..'.$app['config']['upload_path'].'/cache/'.$width.'-'.$file)
            ;
            _log('debug: file resized');
        }catch(Imagine\Exception\Exception $e){
            $app['monolog']->addError($e->getMessage());
            $app->abort(404);
        }
    }

    $format = pathinfo($file, PATHINFO_EXTENSION);

    $response = new Symfony\Component\HttpFoundation\Response();
    $response->headers->set('Content-type', 'image/'.$format);
    $response->setContent($image->get($format));

    return $response;
})->bind('image')->value('width', 600);


/**
 * Image getter. Returns a cropped version of the image. The thumbnail has a fixed height/width of @param $dimension
 * Uses a cached version if it exists, otherwise it creates one and returns that.
 *
 * @param  string       $file       the image name
 * @param  int          $dimnesion  the dimension of the wanted image
 * @return Response                 Response object
 */
$app->get('/thumb/{file}/{dimension}', function($file, $dimension) use ($app){
    try {
        $image = $app['imagine']->open(__DIR__.'/..'.$app['config']['upload_path'].'/cache/thumb-'.$dimension.'-'.$file);
    } catch(Imagine\Exception\Exception $e) {
        try {
            if (!is_dir(__DIR__.'/..'.$app['config']['upload_path'].'/cache/')) {
                mkdir(__DIR__.'/..'.$app['config']['upload_path'].'/cache/', 0777);
            }

            $image = $app['imagine']->open(__DIR__.'/..'.$app['config']['upload_path'].'/'.$file);
            $size = $image->getSize();
            $transformation = new Filter\Transformation();
            $transformation->thumbnail(new Imagine\Image\Box($dimension, $dimension), Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND);
            $image = $transformation
                ->apply($image)
                ->save(__DIR__.'/..'.$app['config']['upload_path'].'/cache/thumb-'.$width.'-'.$file)
            ;
        } catch(Imagine\Exception\Exception $e) {
            $app['monolog']->addError($e->getMessage());
            $app->abort(404);
        }
    }
    $format = pathinfo($file, PATHINFO_EXTENSION);

    $response = new Symfony\Component\HttpFoundation\Response();
    $response->headers->set('Content-type', 'image/'.$format);
    $response->setContent($image->get($format));

    return $response;
})->bind('thumb')->value('dimension', 200);

/**
 * Image uploader
 *
 * @return JSON                 json object
 */
$app->match('/upload', function(Request $request) use ($app) {
    $file = $request->files->get('Filedata');

    $new_filename = $file->getClientOriginalName();
    $new_filename .= '-'.time();
    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';

    if (!is_dir(__DIR__.'/..'.$app['config']['upload_path'].'/tmp/')) {
        mkdir(__DIR__.'/..'.$app['config']['upload_path'].'/tmp/', 0777);
    }

    if ($file->move(__DIR__.'/..'.$app['config']['upload_path'].'/tmp/', $new_filename)) {
        $image = $app['imagine']->open(__DIR__.'/..'.$app['config']['upload_path'].'/tmp/'.$new_filename);
        $size = $image->getSize();
        $transformation = new Filter\Transformation();
        $transformation->resize($size->widen(600));
        $image = $transformation->apply($image)->save(__DIR__.'/..'.$app['config']['upload_path'].'/tmp/'.$new_filename);

        $response = new Symfony\Component\HttpFoundation\Response();
        $response->setContent($new_filename);

        return $response;
    } else {
        return $app->json(array(
            'status' => false,
            'message' => 'Der skete en fejl'
        ));
    }
})->bind('upload');

return $app;

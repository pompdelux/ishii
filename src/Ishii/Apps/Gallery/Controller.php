<?php

namespace Ishii\Apps\Gallery;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use Tobiassjosten\Silex\Provider\FacebookServiceProvider;

class Controller implements ControllerProviderInterface
{
    protected $gallery;

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        // https://github.com/tobiassjosten/FacebookServiceProvider
        $app->register(new FacebookServiceProvider(), array(
            'facebook.app_id' => $app['config']['facebook_apps']['gallery']['app_id'],
            'facebook.secret' => $app['config']['facebook_apps']['gallery']['secret'],
        ));

        if (!$this->gallery) {
            $this->gallery = new Gallery($app);
        }

        $gallery = $this->gallery;

        $controller->match('/{galleryId}', function (Application $app, Request $request, $galleryId) use ($gallery) {
            return $gallery->index($request, $galleryId);
        })->bind('gallery_homepage');

        $controller->match('/add/{galleryId}', function (Application $app, Request $request, $galleryId) use ($gallery) {
            // $user = $app['facebook']->getUser();
            // if(!$user){
            //     return $app->redirect($app['url_generator']->generate('fan_gate'));
            // }

            return $gallery->add($request, $galleryId);
        })->bind('gallery_add');

        $controller->get('/picture/{pictureId}', function (Application $app, Request $request, $pictureId) use ($gallery) {
            // $user = $app['facebook']->getUser();
            // if(!$user){
            //     return $app->redirect($app['url_generator']->generate('fan_gate'));
            // }

            return $gallery->view($request, $pictureId);
        })->bind('gallery_picture');

        $controller->get('/random', function (Application $app, Request $request) use ($gallery) {
            // $user = $app['facebook']->getUser();
            // if(!$user){
            //     return $app->redirect($app['url_generator']->generate('fan_gate'));
            // }

            return $gallery->index($request);
        })->bind('gallery_random');

        $controller->get('/about', function (Application $app, Request $request) use ($gallery) {
            return $gallery->index($request);
        })->bind('gallery_about');

        $controller->get('/tab', function (Application $app, Request $request) {
            return $gallery->tab($request);
        });

        $controller->get('/app', function (Application $app, Request $request) {
            return $gallery->tab($request);
        });

        return $controller;
    }
}

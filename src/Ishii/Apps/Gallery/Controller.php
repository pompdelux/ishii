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

        if (!$this->gallery) {
            $this->gallery = new Gallery($app);
        }


        $gallery = $this->gallery;

        $controller->get('/', function (Application $app, Request $request) use ($gallery) {
            return $gallery->index($request);
        })->bind('gallery_homepage');

        $controller->match('/add', function (Application $app, Request $request) use ($gallery) {
            // $user = $app['facebook']->getUser();
            // if(!$user){
            //     return $app->redirect($app['url_generator']->generate('fan_gate'));
            // }

            return $gallery->add($request);
        })->bind('gallery_add');

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

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
        
        $this->app = $app;
        
        $controller->before(function (Request $request){
            //ugly method to find the gallery id
            $galleryId = explode('/',$request->getPathInfo())[2];

            //do the gallery exists?
            $this->app['gallery'] = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries WHERE id = ? LIMIT 1", array((int) $galleryId));
            if(empty($this->app['gallery'])){
                $this->app->abort(404, $this->app['translator']->trans('404.title'));
            }

            $this->app['facebook']->setAppId($this->app['gallery']['app_id']);
            $this->app['facebook']->setApiSecret($this->app['gallery']['secret']);
        });

        if (!$this->gallery) {
            $this->gallery = new Gallery($app);
        }

        $gallery = $this->gallery;
        
        $controller->match('/{galleryId}', function (Application $app, Request $request, $galleryId) use ($gallery) {
            return $gallery->index($request, $galleryId);
        })->bind('gallery_homepage');

        $controller->match('/{galleryId}/add/', function (Application $app, Request $request, $galleryId) use ($gallery) {
            // $user = $app['facebook']->getUser();
            // if(!$user){
            //     return $app->redirect($app['url_generator']->generate('fan_gate'));
            // }

            return $gallery->add($request, $galleryId);
        })->bind('gallery_add');

        $controller->get('{galleryId}/picture/{pictureId}', function (Application $app, Request $request, $galleryId, $pictureId) use ($gallery) {
            // $user = $app['facebook']->getUser();
            // if(!$user){
            //     return $app->redirect($app['url_generator']->generate('fan_gate'));
            // }

            return $gallery->view($request, $galleryId, $pictureId);
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

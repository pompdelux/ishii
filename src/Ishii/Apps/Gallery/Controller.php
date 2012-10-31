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
            $path = explode('/',$request->getPathInfo());
            $galleryId = $path[2];

            //do the gallery exists?
            $this->app['gallery'] = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries WHERE id = ? LIMIT 1", array((int) $galleryId));
            if(empty($this->app['gallery'])){
                $this->app->abort(404, $this->app['translator']->trans('404.title'));
            }

            $this->app['facebook']->setAppId($this->app['gallery']['app_id']);
            $this->app['facebook']->setApiSecret($this->app['gallery']['secret']);

            //Get the app_data field from signed request.
            $signed_request = $this->app['facebook']->getSignedRequest();
            print_r($signed_request);
            if(isset($signed_request['page'])){

                // Redirect to the right picture if app_data is set
                if(isset($signed_request['app_data'])){
                    //return $this->app->redirect(
                        die('<script>top.location=\''.$this->app['url_generator']->generate('gallery_picture', array('galleryId' => $galleryId, 'pictureId' => $signed_request['app_data'])).'\'</script>');//);
                }
            }

            // If the user comes from a link, ousite of iframe, redirect to the appropiate url on page
            $referer = $request->server->get('HTTP_REFERER');
            if(strpos($referer, 'facebook.com') AND !isset($signed_request['page'])){
                $fb_app_data = $galleryId;
                if(isset($path[4])){ // Ugly method to find pictureId
                    $fb_app_data .= '|'.$path[4];
                }
                return $this->app->redirect('http://facebook.com/pages/'.$this->app['config']['facebook_apps']['default']['page'].'?sk=app_'.$this->app['gallery']['app_id'].'&app_data='.$fb_app_data);
            }
        });

        if (!$this->gallery) {
            $this->gallery = new Gallery($app);
        }

        $gallery = $this->gallery;
        
        $controller->match('/{galleryId}/{offset}', function (Application $app, Request $request, $galleryId, $offset) use ($gallery) {
            return $gallery->gallery($request, $galleryId, $offset);
        })
        ->bind('gallery_homepage')
        ->value('offset', 0);

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

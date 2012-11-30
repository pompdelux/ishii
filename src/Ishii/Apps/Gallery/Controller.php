<?php

namespace Ishii\Apps\Gallery;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use Tobiassjosten\Silex\Provider\FacebookServiceProvider;
use Mobile_Detect;

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
            $this->app['gallery'] = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries WHERE id = ? AND active = TRUE LIMIT 1", array((int) $galleryId));
            if (empty($this->app['gallery'])) {
                $this->app->abort(404, $this->app['translator']->trans('404.gallery.not.found.title'));
            }

            $this->app['page']->setGallery($this->app['gallery']);

            if (!empty($this->app['gallery']['app_id']) &&
                !empty($this->app['gallery']['secret']) &&
                !empty($this->app['gallery']['page_url'])
            ) {
                $facebook_app['app_id'] = $this->app['gallery']['app_id'];
                $facebook_app['secret'] = $this->app['gallery']['secret'];
                $facebook_app['page_url'] = $this->app['gallery']['page_url'];
                $facebook_app['page_id'] = $this->app['gallery']['page_id'];
            } else {
                $facebook_app['app_id'] = $this->app['config']['facebook_apps']['default']['app_id'];
                $facebook_app['secret'] = $this->app['config']['facebook_apps']['default']['secret'];
                $facebook_app['page_url'] = $this->app['config']['facebook_apps']['default']['page_url'];
                $facebook_app['page_id'] = $this->app['config']['facebook_apps']['default']['page_id'];
            }

            $this->app['page']->setFacebook($facebook_app); // Used in twig
            $this->app['facebook']->setAppId($this->app['gallery']['app_id']);
            $this->app['facebook']->setApiSecret($this->app['gallery']['secret']);

            try {
                //Get the signed request from facebook.
                $signed_request = $this->app['facebook']->getSignedRequest();
            } catch(Exception $e) {
                $this->app['monolog']->addError($e->getMessage());
                $this->app->abort(500);
            }

            if (isset($signed_request['page'])) {
                if ($signed_request['page']['id'] != $this->app['gallery']['page_id']) {
                    $this->app['monolog']->addError('Wrong Setup for iframe: db['.json_encode($this->app['gallery']).'], page_id['.$signed_request['page']['id'].']');
                    $this->app->abort(404, 'Din FB opsæting er desværre forkert. Kontakt en administrator!');
                }

                if (!$signed_request['page']['liked']) {
                    return $this->app->render("Gallery/fangate.twig");
                }

                // Redirect to the right picture if app_data is set
                if (isset($signed_request['app_data'])) {
                    $pictureId = explode('|', $signed_request['app_data'])[1]; // galleryId|pictureId
                    die('<script>location.href=\''.$this->app['url_generator']->generate('gallery_picture', array('galleryId' => $galleryId, 'pictureId' => $pictureId)).'\'</script>');
                }

            }

            // Detect which device the user are using.
            $detect = new Mobile_Detect();

            // If the user comes from a link, ousite of iframe, redirect to the appropiate url on page. But only if not mobile!
            $referer = $request->server->get('HTTP_REFERER');
            if (strpos($referer, 'facebook.com') AND !isset($signed_request['page']) AND (!$detect->isMobile() AND !$detect->isTablet())) {

                $fb_app_data = $galleryId;
                if (isset($path[4])) { // Ugly method to find pictureId
                    $fb_app_data .= '|'.$path[4];
                }
                if($this->app['debug']){
                    $this->app['monolog']->addInfo('Redirecting to '.$this->app['page']['facebook']['page_url'].'?app_data='.$fb_app_data);
                    $this->app['monolog']->addInfo(json_encode($signed_request));
                }

                return $this->app->redirect($this->app['page']['facebook']['page_url'].'?app_data='.$fb_app_data);
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
            return $gallery->add($request, $galleryId);
        })->bind('gallery_add');

        $controller->get('{galleryId}/picture/{pictureId}', function (Application $app, Request $request, $galleryId, $pictureId) use ($gallery) {
            return $gallery->view($request, $galleryId, $pictureId);
        })->bind('gallery_picture');

        $controller->get('/random', function (Application $app, Request $request) use ($gallery) {
            return $gallery->index($request);
        })->bind('gallery_random');

        $controller->get('/about', function (Application $app, Request $request) use ($gallery) {
            return $gallery->index($request);
        })->bind('gallery_about');

        return $controller;
    }
}

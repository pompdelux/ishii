<?php

namespace Ishii\Apps\Admin;

use Silex\Application;
use Silex\Route;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

use Tobiassjosten\Silex\Provider\FacebookServiceProvider;

class Controller implements ControllerProviderInterface
{
    use Route\SecurityTrait;
    protected $admin;

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        
        $this->app = $app;
        if (!$this->admin) {
            $this->admin = new Admin($app);
        }

        $admin = $this->admin;

        $controller->get('/', function (Application $app, Request $request) use ($admin) {
            return $admin->index($request);
        })->bind('admin_galleries');

        $controller->get('/add', function (Application $app, Request $request) use ($admin) {
            return $admin->edit($request);
        })->bind('admin_gallery_add');

        $controller->get('/edit/{id}', function (Application $app, Request $request, $id) use ($admin) {
            return $admin->edit($request, $id);
        })->bind('admin_gallery_edit');

        return $controller;
    }
}

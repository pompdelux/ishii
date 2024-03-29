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

        $controller->get('/pictures/{id}/{offset}', function (Application $app, Request $request, $id, $offset) use ($admin) {
            return $admin->pictures($request, $id, $offset);
        })
        ->bind('admin_gallery_pictures')
        ->value('offset', 0);

        $controller->match('/add', function (Application $app, Request $request) use ($admin) {
            return $admin->edit($request);
        })->bind('admin_gallery_add');

        $controller->match('/edit/{id}', function (Application $app, Request $request, $id) use ($admin) {
            return $admin->edit($request, $id);
        })->bind('admin_gallery_edit');

        $controller->get('/delete/{id}', function (Application $app, Request $request, $id) use ($admin) {
            return $admin->delete_gallery($request, $id);
        })->bind('admin_gallery_delete');

        $controller->get('/picture/toggle-active/{id}', function (Application $app, Request $request, $id) use ($admin) {
            return $admin->picture_toggle_active($request, $id);
        })->bind('admin_gallery_picture_toggle_active');

        $controller->get('/winners/{id}/{count}', function (Application $app, Request $request, $id, $count) use ($admin) {
            return $admin->pull_winners($request, $id, $count);
        })
        ->bind('admin_gallery_pull_winners')
        ->value('count', 3);

        return $controller;
    }
}

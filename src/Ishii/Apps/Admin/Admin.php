<?php

namespace Ishii\Apps\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application\UrlGeneratorTrait;
use Imagine\Filter\Transformation;
use Imagine\Image\Box;

class Admin
{

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * index method should only be used for documentation and stuff.
     *
     * @param  Request  $request    Request object
     * @param  Int      $galleryId  the gallery id
     * @return Response             Response object
     */
    public function index(Request $request, $galleryId)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';

        //$gallery_pictures = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE gallery_id = ? ", array((int) $galleryId));

        //$this->app['page']->setImages(array($gallery_pictures));

        return $this->app->render("Admin/index.twig");
    }

    /**
     * index method should only be used for documentation and stuff.
     *
     * @param  Request  $request    Request object
     * @param  Int      $galleryId  the gallery id
     * @return Response             Response object
     */
    public function edit(Request $request, $id = NULL)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';

        //$gallery_pictures = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE gallery_id = ? ", array((int) $galleryId));

        //$this->app['page']->setImages(array($gallery_pictures));

        return $this->app->render("Admin/index.twig");
    }
}

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
     * index method, show all galleries
     *
     * @param  Request  $request    Request object
     * @return Response             Response object
     */
    public function index(Request $request)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';

        $galleries = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries", array());

        $this->app['page']->setGalleries(array($galleries));

        return $this->app->render("Admin/index.twig");
    }

    /**
     * Edit an gallery method
     *
     * @param  Request  $request    Request object
     * @param  Int      $id         the gallery id
     * @return Response             Response object
     */
    public function edit(Request $request, $id = NULL)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';
        $gallery = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries WHERE id = ?", array((int)$id));
        
        $this->app['page']->setGallery(array($gallery));

        return $this->app->render("Admin/index.twig");
    }

    /**
     * See all pictures uploaded to a gallery
     *
     * @param  Request  $request    Request object
     * @param  Int      $id         the gallery id
     * @return Response             Response object
     */
    public function pictures(Request $request, $id, $offset)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';

        $gallery_pictures = $this->app['db']->fetchAll("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE p.gallery_id = ? ORDER BY p.created_date DESC LIMIT {$offset},50", array((int) $id));
        $total_pictures = $this->app['db']->fetchColumn("SELECT COUNT(id) FROM gallery_pictures WHERE gallery_id = ?", array((int)$galleryId));

        $this->app['page']->setPictures(array($gallery_pictures));
        
        if($offset > 0){
            $this->app['page']->setPrev(array('active' => true, 'offset' => $offset - 10));
        }

        if(($total_pictures - 10) > $offset){
            $this->app['page']->setNext(array('active' => true, 'offset' => $offset + 10));
        }
        

        return $this->app->render("Admin/index.twig");
    }
}

<?php

namespace Ishii\Apps\Gallery;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application\UrlGeneratorTrait;

class Gallery
{
    use Traits\TabTrait;
    use Traits\AppTrait;

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->user = $this->app['facebook']->getUser();
    }

    /**
     * index method should only be used for documentation and stuff.
     *
     * @param  Request $request Request object
     * @return Response         Response object
     */
    public function index(Request $request)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';

        $this->app['page']->setGallery(array('images' => array()));

        return $this->app->render("Gallery/index.twig");
    }

    public function add(Request $request, $galleryId)
    {
        if(!$galleryId){
            $app->abort(404, $app['translator']->trans('400.title'));
        }

        $gallery = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries WHERE id = ? ", array((int) $galleryId));

        if(!$gallery['is_open']){ // TODO: der skal laves en fin side! 
            $this->app->abort(404, $this->app['translator']->trans('404.title'));
        }

        if(!$this->user AND !$this->app['debug']){
            $this->app->abort(404, $this->app['translator']->trans('facebook.user.login.error'));
            //return $this->app->redirect('/');
        }

        $form = $this->app['form.factory']->createBuilder('form')
            ->add('picture', 'file')
            ->add('description', 'textarea', array(
                'required' => false,
                'max_length' => 255
            ))
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $file = $form['picture']->getData();
                
                $new_filename = ($user)?$user:'no-user';
                $new_filename .= '-'.time();
                $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';
                
                if($file->move(__DIR__.'/../../../../'.$this->app['config']['upload_path'], $new_filename)){
                    $post = $this->app['db']->insert('gallery_pictures', array(
                        'gallery_id' => 1,
                        'uid' => $this->user,
                        'url' => $new_filename,
                        'description' => $data['description'],
                        'created_date' => date("Y-m-d H:i:s")
                    ));
                    return $this->app->redirect($this->app->url('gallery_picture', array('pictureId' => $this->app['db']->lastInsertId())));
                }
            }
        }

        return $this->app->render("Gallery/add.twig", array(
            'form' => $form->createView(),
            'galleryId' => $galleryId
        ));
    }

    public function view(Request $request, $pictureId)
    {
        $picture = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures WHERE id = ? AND active = TRUE ", array((int) $pictureId));

        if(!$picture){
            $this->app->abort(404, $this->app['translator']->trans('404.title'));
        }

        return $this->app->render("Gallery/picture_view.twig", array(
            'galleryId' => $picture['gallery_id'],
            'pictureId' => $pictureId,
            'picture' => $picture
        ));
    }
}

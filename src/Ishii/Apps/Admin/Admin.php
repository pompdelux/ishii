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

        // Ugly hack. Form couldn't see '1' as TRUE
        $gallery['active'] = $gallery['active']?TRUE:FALSE;
        $gallery['is_open'] = $gallery['is_open']?TRUE:FALSE;

        $form = $this->app['form.factory']->createBuilder('form', $gallery)
            ->add('title', 'text', array('required' => true))
            ->add('description', 'textarea', array('required' => false))
            ->add('top_image', 'file', array('required' => false))
            ->add('bottom_image', 'file', array('required' => false))
            ->add('fangate_image', 'file', array('required' => false))
            ->add('uploadform_image', 'file', array('required' => false))
            ->add('active', 'checkbox', array('required' => false))
            ->add('is_open', 'checkbox', array('required' => false))
            ->add('app_id', 'text', array('required' => false))
            ->add('secret', 'text', array('required' => false))
            ->getForm()
        ;
        if ('POST' == $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();


                /**
                *
                * Upload pictures to a certain folder. Maybe it can iterate trhough the files array. But this was easier for now.
                *
                */
                $uploaded_files = array();
                $messages = array();
                if($file = $form['top_image']->getData()){
                    $new_filename = $gallery['id'].'-top_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';
                    
                    if($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)){
                        $uploaded_files['top_image'] = $new_filename;
                    }else{
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }
                if($file = $form['bottom_image']->getData()){
                    $new_filename = $gallery['id'].'-bottom_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';
                    
                    if($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)){
                        $uploaded_files['bottom_image'] = $new_filename;
                    }else{
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }
                if($file = $form['fangate_image']->getData()){
                    $new_filename = $gallery['id'].'-fangate_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';
                    
                    if($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)){
                        $uploaded_files['fangate_image'] = $new_filename;
                    }else{
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }
                if($file = $form['uploadform_image']->getData()){
                    $new_filename = $gallery['id'].'-uploadform_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';
                    
                    if($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)){
                        $uploaded_files['uploadform_image'] = $new_filename;
                    }else{
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }

                $data_to_save = array(
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'active' => $data['active'],
                    'is_open' => $data['is_open'],
                    'app_id' => $data['app_id'],
                    'secret' => $data['secret'],
                );
                $data_to_save = array_merge($data_to_save, $uploaded_files);

                $post = $this->app['db']->update('gallery_galleries', 
                    $data_to_save,
                    array('id' =>$gallery['id'])
                );
                $messages[] = array(
                    'type' => 'info',
                    'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                    'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                );
            }
        }


        return $this->app->render("Admin/edit_gallery.twig", array(
            'form' => $form->createView(),
            'flash' => $messages
        ));
    }

    /**
     * delete method, delete gallery ajax style
     *
     * @param  Request  $request    Request object
     * @return Response             Response object
     */
    public function delete_gallery(Request $request, $id)
    {

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Seneste';

        $galleries = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries", array());

        $this->app['page']->setGalleries(array($galleries));

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
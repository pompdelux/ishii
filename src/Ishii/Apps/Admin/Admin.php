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

        $galleries = $this->app['db']->fetchAll("SELECT * FROM gallery_galleries", array());

        $this->app['page']->setGalleries($galleries);

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
        if($id){
            $gallery = $this->app['db']->fetchAssoc("SELECT * FROM gallery_galleries WHERE id = ?", array((int)$id));

            // Ugly hack. Form couldn't see '1' as TRUE
            $gallery['active'] = $gallery['active']?TRUE:FALSE;
            $gallery['is_open'] = $gallery['is_open']?TRUE:FALSE;

            $this->app['page']->setGallery(array($gallery));
        }


        $form = $this->app['form.factory']->createBuilder('form', $gallery)
            ->add('title', 'text', array(
                'required' => true,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.title')
            ))
            ->add('description', 'textarea', array(
                'required' => true,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.description')
            ))
            ->add('top_image', 'file', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.top_image')
            ))
            ->add('top_submit_button', 'text', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.top_submit_button')
            ))
            ->add('bottom_image', 'file', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.bottom_image')
            ))
            ->add('fangate_image', 'file', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.fangate_image')
            ))
            ->add('uploadform_image', 'file', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.uploadform_image')
            ))
            ->add('button_color', 'text', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.button_color')
            ))
            ->add('button_color_hover', 'text', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.button_color_hover')
            ))
            ->add('active', 'checkbox', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.active')
            ))
            ->add('is_open', 'checkbox', array(
                'required' => false,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.is_open')
            ))
            ->add('app_id', 'text', array(
                'required' => true,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.app_id')
            ))
            ->add('secret', 'text', array(
                'required' => true,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.secret')
            ))
            ->add('page_url', 'text', array(
                'required' => true,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.page_url')
            ))
            ->add('page_id', 'text', array(
                'required' => true,
                'label' => $this->app['translator']->trans('admin.add.gallery.label.page_id')
            ))
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

                if ($file = $form['top_image']->getData()) {
                    $new_filename = $gallery['id'].'-top_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';

                    if ($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)) {
                        $uploaded_files['top_image'] = $new_filename;
                    } else {
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }

                if ($file = $form['bottom_image']->getData()) {
                    $new_filename = $gallery['id'].'-bottom_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';

                    if ($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)) {
                        $uploaded_files['bottom_image'] = $new_filename;
                    } else {
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }

                if ($file = $form['fangate_image']->getData()) {
                    $new_filename = $gallery['id'].'-fangate_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';

                    if ($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)) {
                        $uploaded_files['fangate_image'] = $new_filename;
                    } else {
                        $messages[] = array(
                            'type' => 'error',
                            'short' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.short'),
                            'ext' => $this->app['translator']->trans('admin.new.picture.uploaded.failed.description')
                        );
                    }
                }

                if ($file = $form['uploadform_image']->getData()) {
                    $new_filename = $gallery['id'].'-uploadform_image';
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';

                    if ($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path_gallery'], $new_filename)) {
                        $uploaded_files['uploadform_image'] = $new_filename;
                    } else {
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
                    'top_submit_button' => $data['top_submit_button'],
                    'button_color' => $data['button_color'],
                    'button_color_hover' => $data['button_color_hover'],
                    'active' => $data['active'],
                    'is_open' => $data['is_open'],
                    'app_id' => $data['app_id'],
                    'secret' => $data['secret'],
                    'page_url' => $data['page_url'],
                    'page_id' => $data['page_id'],
                );
                $data_to_save = array_merge($data_to_save, $uploaded_files);

                if ($id) {
                    $post = $this->app['db']->update('gallery_galleries',
                        $data_to_save,
                        array('id' =>$gallery['id'])
                    );
                } else {
                    $this->app['db']->insert('gallery_galleries', $data_to_save);
                    $messages[] = array(
                        'type' => 'success',
                        'short' => $this->app['translator']->trans('admin.gallery.updated.short'),
                        'ext' => $this->app['translator']->trans('admin.gallery.updated.description')
                    );

                    return $this->app->redirect($this->app->url('admin_gallery_edit', array('id' => $this->app['db']->lastInsertId())));
                }

                $messages[] = array(
                    'type' => 'info',
                    'short' => $this->app['translator']->trans('admin.gallery.updated.short'),
                    'ext' => $this->app['translator']->trans('admin.gallery.updated.description')
                );
            }
        }


        return $this->app->render("Admin/edit_gallery.twig", array(
            'form' => $form->createView(),
            'flash' => $messages
        ));
    }

    /**
     * set inactive method, picture ajax style
     *
     * @param  Request  $request    Request object
     * @param  integer  $id         The picture ID
     * @return Response             Response object
     */
    public function picture_toggle_active(Request $request, $id)
    {
        $picture = $this->app['db']->fetchAssoc("SELECT active FROM gallery_pictures WHERE id = ?", array((int)$id));

        if ($image = $this->app['db']->update('gallery_pictures',
            array('active' => !$picture['active']),
            array('id' => $id)
        )) {
            return $this->app->json(array(
                'status' => true,
                'message' => 'Billede er blevet toggled'
            ));
        }
    }

    /**
     * delete method, delete gallery ajax style
     *
     * @param  Request  $request    Request object
     * @return Response             Response object
     */
    public function delete_gallery(Request $request, $id)
    {
        if ($image = $this->app['db']->delete('gallery_galleries', array('id' => $id))) {
            return $this->app->json(array(
                'status' => true,
                'message' => 'Galleriet er blevet slettet!'
            ));
        }

        return $this->app->json(array(
            'status' => false,
            'message' => 'Der skete en fejl'
        ));
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
        $total_pictures = $this->app['db']->fetchColumn("SELECT COUNT(id) FROM gallery_pictures WHERE gallery_id = ?", array((int)$id));

        $this->app['page']->setPictures($gallery_pictures);
        $this->app['page']->setGalleryId($id);

        if ($offset > 0) {
            $this->app['page']->setPrev(array('active' => true, 'offset' => $offset - 10));
        }

        if (($total_pictures - 10) > $offset) {
            $this->app['page']->setNext(array('active' => true, 'offset' => $offset + 10));
        }

        return $this->app->render("Admin/pictures.twig");
    }

    /**
     * See all pictures uploaded to a gallery
     *
     * @param  Request  $request    Request object
     * @param  Int      $id         the gallery id
     * @return Response             Response object
     */
    public function pull_winners(Request $request, $id, $count)
    {
        if($request->query->get('count')) {
            $count = $request->query->get('count');
        }

        $this->app['page']['title'] = 'POMPdeLUX Facebook galleri';
        $this->app['page']['browser_title'] = ' » Vindere';

        $gallery_pictures = $this->app['db']->fetchAll("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE p.gallery_id = ? ORDER BY RAND() LIMIT {$count}", array((int) $id));

        $this->app['page']->setGalleryId($id);
        $this->app['page']->setPictures($gallery_pictures);

        return $this->app->render("Admin/pictures.twig");
    }
}

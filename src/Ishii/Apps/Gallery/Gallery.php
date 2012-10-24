<?php

namespace Ishii\Apps\Gallery;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function add(Request $request)
    {
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

                $post = $this->app['db']->insert('pictures', array(
                    'gallery_id' => 1,
                    'uid' => $this->user,
                    'description' => $data['description'],
                    'created_date' => date("Y-m-d H:i:s")
                ));

                print_r($this->user);
                return new Response('Thank you for your feedback!', 201);
            }
        }

        return $this->app->render("Gallery/add.twig", array('form' => $form->createView()));
    }
}

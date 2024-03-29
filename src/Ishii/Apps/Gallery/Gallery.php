<?php

namespace Ishii\Apps\Gallery;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Silex\Application\UrlGeneratorTrait;
use Imagine\Filter\Transformation;
use Imagine\Image\Box;
use Symfony\Component\Validator\Constraints as Assert;
use Tobiassjosten\Silex\Provider\FacebookServiceProvider;

class Gallery
{
    use Traits\TabTrait;
    use Traits\AppTrait;

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

        $gallery_pictures = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE gallery_id = ? ", array((int) $galleryId));

        $this->app['page']->setImages(array($gallery_pictures));

        return $this->app->render("Gallery/index.twig");
    }

    /**
     * Gallery. Page to show all pictures in a gallery
     *
     * @param  Request $request Request object
     * @return Response         Response object
     */
    public function gallery(Request $request, $galleryId, $offset)
    {
        $this->app['page']['title'] = $this->app['page']['gallery']['title'];
        $this->app['page']['browser_title'] = $this->app['page']['gallery']['title'];

        $gallery_pictures = $this->app['db']->fetchAll("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE p.gallery_id = ? AND active = TRUE ORDER BY p.created_date DESC LIMIT {$offset},5", array((int) $galleryId));
        $total_pictures = $this->app['db']->fetchColumn("SELECT COUNT(id) FROM gallery_pictures WHERE gallery_id = ? AND active = TRUE", array((int)$galleryId));

        // /* 
        //  * Method to return all stats for a given url from facebook
        //  * Very slow if it iterates through many urls. use with caution!
        //  */
        // foreach ($gallery_pictures as &$picture) {
        //         $param  =   array(
        //             'method'    => 'fql.query',
        //             'query'     => "SELECT url, share_count, like_count, comment_count, total_count, click_count FROM link_stat WHERE url = '".$request->getUri()."'",
        //             'callback'  => ''
        //         );
        //     $link_stat = $this->app['facebook']->api($param);
        //     //print_r($link_stat);
        // }

        $this->app['page']->setImages($gallery_pictures);
        
        if($offset > 0){
            $this->app['page']->setPrev(array('active' => true, 'offset' => $offset - 5));
        }

        if(($total_pictures - 5) > $offset){
            $this->app['page']->setNext(array('active' => true, 'offset' => $offset + 5));
        }
            

        return $this->app->render("Gallery/index.twig", 
            array(),
            new Response('',200,array('P3P' => 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"')) // Old browser fuckbug. Ie in a nutshell!
        );
    }


    /**
     * Add method to add a picture to a gallery.
     *
     * @param  Request  $request    Request object
     * @param  Int      $galleryId  the gallery id
     * @return Response             Response object
     */
    public function add(Request $request, $galleryId)
    {
        $user_id = $this->app['facebook']->getUser();

        // These are to append on the URL on the POST form.
        $state = $request->query->get('state'); // From Facebook auth page
        $code = $request->query->get('code');
        $token = $request->query->get('token');
        if(!$token)
            $token = $this->app['facebook']->getAccessToken();
        else{
            $this->app['facebook']->setAccessToken($token);
            $this->app['facebook']->setExtendedAccessToken();
        }
        $this->app['page']->setState(!empty($state)?$state:'');
        $this->app['page']->setCode(!empty($code)?$code:'');
        $this->app['page']->setToken(!empty($token)?$token:'');

        if($this->app['debug'])
        {
            $this->app['monolog']->addInfo('query: '.isset($_REQUEST['state'])?$_REQUEST['state']:'NAN');
            $this->app['monolog']->addInfo($request);
            $this->app['monolog']->addInfo('User ID: '.$user_id);
        }
        if($user_id)
        {
            try{
                $this->app->user = $this->app['facebook']->api('/me');
                $this->app['page']->setUser(array($this->app->user));
                if($this->app['debug']){
                    $this->app['monolog']->addInfo('Facebook User: '.json_encode($this->app->user));
                }
            }catch(\FacebookApiException $e){
                $this->app['monolog']->addError('FacebookERR0R '.$e->getMessage());
                $this->app->user = null;
            }
        }
        if(!$this->app->user)
        {
            if($this->app['debug'])
            {
                $this->app['monolog']->addInfo('Redirecting to FB');
                $this->app['monolog']->addInfo('User: '.json_encode($this->app->user));
                $this->app['monolog']->addInfo('State: '.$state.' \n Code: '.$code);
            }
            return $this->app->redirect($this->app['facebook']->getLoginUrl(array(
                'scope' => 'email',
                'redirect_uri' => $this->app->url('gallery_add', array('galleryId' => $galleryId)),
                'display' => 'popup'
            )));
        }

        if(!$this->app['gallery']['is_open']){ // TODO: der skal laves en fin side! 
            $this->app->abort(404, $this->app['translator']->trans('404.title'));
        }

        $form = $this->app['form.factory']->createBuilder('form')
            ->add('picture', 'file', array(
                'label' => $this->app['translator']->trans('gallery.upload.picture.label'),
                'constraints' => new Assert\Image()
            ))
            ->add('title', 'text', array(
                'required' => false,
                'label' => $this->app['translator']->trans('gallery.upload.title.label')
            ))
            ->add('description', 'textarea', array(
                'required' => false,
                'max_length' => 255,
                'label' => $this->app['translator']->trans('gallery.upload.description.label')
            ))
            ->add('accept_conditions', 'checkbox', array(
                'required' => true,
                'label' => $this->app['translator']->trans('gallery.upload.accept.conditions.label')
            ))
            ->add('uuid', 'hidden')
            ->add('tmp_file', 'hidden')
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                
                $data = $form->getData();

                $db_user = $this->app['db']->fetchAssoc("SELECT * FROM gallery_users WHERE uid = ? ", array((int) $this->app->user['id']));
                if(!$db_user){
                    $db_user = $this->app['db']->insert('gallery_users', array(
                        'uid' => $this->app->user['id'],
                        'first_name' => $this->app->user['first_name'],
                        'last_name' => $this->app->user['last_name'],
                        'email' => $this->app->user['email'],
                        'gender' => $this->app->user['gender'],
                        'link' => $this->app->user['link'],
                        'ip' => $request->getClientIp(),
                        'authorized_date' => date("Y-m-d H:i:s"),
                        'last_seen_date' => date("Y-m-d H:i:s")
                    ));
                }
                try{
                    if(!empty($data['tmp_file']))
                        $file = new File(__DIR__.'/../../../..'.$this->app['config']['upload_path'].'/tmp/'.$data['tmp_file']);
                    else
                        $file = $form['picture']->getData();
                    
                    $new_filename = $this->app->user['id'];
                    $new_filename .= '-'.time();
                    $new_filename .= ($file->guessExtension())?'.'.$file->guessExtension():'.bin';
                
                    if($file->move(__DIR__.'/../../../..'.$this->app['config']['upload_path'], $new_filename)){
                        $image = $this->app['imagine']->open(__DIR__.'/../../../..'.$this->app['config']['upload_path'].'/'.$new_filename);
                        $size = $image->getSize();
                        $transformation = new Transformation();
                        $transformation->resize($size->widen(600));
                        $image = $transformation->apply($image)->save(__DIR__.'/../../../..'.$this->app['config']['upload_path'].'/'.$new_filename);

                        $post = $this->app['db']->insert('gallery_pictures', array(
                            'gallery_id' => $this->app['gallery']['id'],
                            'uid' => $this->app->user['id'],
                            'url' => $new_filename,
                            'description' => $data['description'],
                            'title' => $data['title'],
                            'created_date' => date("Y-m-d H:i:s")
                        ));

                        $this->app['session']->set('flash', array(
                            'type' => 'success',
                            'short' => $this->app['translator']->trans('new.picture.is.uploaded.short'),
                            'ext' => $this->app['translator']->trans('new.picture.is.uploaded.description')
                        ));

                        return $this->app->redirect($this->app->url('gallery_picture', array('pictureId' => $this->app['db']->lastInsertId(), 'galleryId' => $this->app['gallery']['id'])));
                    }else{
                        $this->app['monolog']->addInfo('ab: File could not be moved?');
                    }
                }catch(Imagine\Exception\Exception $e){
                    $this->app['monolog']->addError($e->getMessage());
                    $this->app->abort(500);
                }
            }
        }

        return $this->app->render("Gallery/add.twig", array(
            'form' => $form->createView(),
            'galleryId' => $galleryId
        ), new Response('',200,array('P3P' => 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'))); // Old browser fuckbug. Ie in a nutshell!
    }

    /**
     * view of a picture in a gallery
     *
     * @param  Request  $request    Request object
     * @param  Int      $galleryId  the gallery id
     * @return Response             Response object
     */
    public function view(Request $request, $galleryId, $pictureId)
    {
        
        $picture = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures p LEFT JOIN gallery_users u ON (u.uid = p.uid) WHERE p.id = ? AND p.gallery_id = ? AND active = TRUE ", array((int) $pictureId, (int)$galleryId));
        //$all_pictures = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures WHERE gallery_id = ? AND active = TRUE ", array((int)$galleryId));

        // Det her er da usædvanligt grimt. Er der ikke en nemmere måde at lave next/prev knapper på specifikke rækker? Hmmm
        $prev_picture = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures WHERE id < ? AND gallery_id = ? AND active = TRUE ORDER BY id DESC", array((int) $pictureId, (int)$galleryId));
        $next_picture = $this->app['db']->fetchAssoc("SELECT * FROM gallery_pictures WHERE id > ? AND gallery_id = ? AND active = TRUE ORDER BY id ASC", array((int) $pictureId, (int)$galleryId));

        if(!$picture){
            $this->app->abort(404, $this->app['translator']->trans('404.picture.not.found.title'));
        }

        $this->app['page']->setPicture($picture);
        $this->app['page']->setNext($next_picture);
        $this->app['page']->setPrev($prev_picture);

        $this->app['page']['title'] = $this->app['page']['picture']['title'] . ' - ' . $this->app['page']['gallery']['title'];
        $this->app['page']['browser_title'] = $this->app['page']['picture']['title'] . ' ' . $this->app['page']['gallery']['title'];
        
        //$this->app['page']->setAllPictures($all_pictures);
        return $this->app->render("Gallery/picture.twig",
            array(),
            new Response('',200,array('P3P' => 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"')) // Old browser fuckbug. Ie in a nutshell!
        );

    }
}

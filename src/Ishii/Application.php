<?php

namespace Ishii;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SecurityServiceProvider;

use Tobiassjosten\Silex\Provider\FacebookServiceProvider;
use Grom\Silex\ImagineServiceProvider;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Loader\YamlFileLoader;
//use Predis\Silex\PredisServiceProvider;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class Application extends SilexApplication
{
    use SilexApplication\TwigTrait;
    use SilexApplication\TranslationTrait;
    use SilexApplication\UrlGeneratorTrait;

    public function __construct()
    {
        parent::__construct();

        /* Encode password with silex */
        /*$encoder = new MessageDigestPasswordEncoder('sha512', true, 3);
        print_r($encoder->encodePassword('dit-password'));
        /* End Encode password with silex */

        $this['config'] = $this->share(function() {
            return Yaml::parse(__DIR__.'/Resources/Config/app.yaml');
        });

        $this['security.firewalls'] = array(
            'admin' => array(
                'pattern' => '^/admin',
                'http' => true,
                'users' => array(
                    //'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='), //foo
                    'admin' => array('ROLE_ADMIN', 'bVIS8V2EpwUfMoPcULfMn9n41X4i8ycyjiOL3h+FvSPJTkJBvvVdBV1RvMwv/loqGK3JjQ6TUVtWGScbK0pcMA=='),
                )
            ),
        );
        $this->register(new SecurityServiceProvider());

        // Define in which grade to encode the password
        $this['security.encoder.digest'] = $this->share(function ($app) {
            return new MessageDigestPasswordEncoder('sha512', true, 3);
        });

        $this['page'] = $this->share(function () {
            return new \Ishii\Page();
        });

        // https://github.com/tobiassjosten/FacebookServiceProvider
        $this->register(new FacebookServiceProvider(), array(
            'facebook.app_id' => $this['config']['facebook_apps']['default']['app_id'],
            'facebook.secret' => $this['config']['facebook_apps']['default']['secret']
        ));

        $this->register(new SessionServiceProvider());
        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/Resources/Templates',
        ));

        $this->register(new FormServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'    => $this['config']['db']['driver'],
                'host'      => $this['config']['db']['host'],
                'dbname'    => $this['config']['db']['dbname'],
                'user'      => $this['config']['db']['user'],
                'password'  => $this['config']['db']['password'],
            ),
        ));

        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->setCache(__DIR__.'/../../tmp/cache/');
            $twig->addGlobal('base_url', $app['request']->getSchemeAndHttpHost().'/');
            $twig->addGlobal('cdn_url', $app['config']['cdn']);
            $twig->addGlobal('page', $app['page']);
            $twig->addGlobal('facebook_apps', $app['config']['facebook_apps']);
            $twig->addGlobal('upload_path', $app['config']['upload_path']);
            $twig->addGlobal('upload_path_gallery', $app['config']['upload_path_gallery']);

            return $twig;
        }));

        $this->register(new TranslationServiceProvider(), array(
            'locale' => $this['config']['locale'],
            'locale_fallback' => $this['config']['locale'],
        ));

        $this['translator'] = $this->share($this->extend('translator', function($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('xliff', __DIR__.'/Resources/Translations/da.xlf', 'da');
            return $translator;
        }));

        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => __DIR__.'/../../logs/development.log',
        ));

        $this->register(new ImagineServiceProvider(), array(
            'imagine.factory' => 'Gd',
            'imagine.base_path' => __DIR__.'/vendor/imagine',
        ));
    }
}

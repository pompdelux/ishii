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

use Tobiassjosten\Silex\Provider\FacebookServiceProvider;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Loader\YamlFileLoader;
//use Predis\Silex\PredisServiceProvider;

class Application extends SilexApplication
{
    use SilexApplication\TwigTrait;
    use SilexApplication\TranslationTrait;
    use SilexApplication\UrlGeneratorTrait;

    public function __construct()
    {
        parent::__construct();

        $this['config'] = $this->share(function() {
            return Yaml::parse(__DIR__.'/Resources/Config/app.yaml');
        });

        $this['page'] = $this->share(function () {
            return new \Ishii\Page();
        });
        
        $this->register(new SessionServiceProvider());

        $this->register(new UrlGeneratorServiceProvider());

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__.'/Resources/Templates',
        ));

        $this->register(new FormServiceProvider());

        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'    => $this['config']['db']['driver'],
                'host'      => $this['config']['db']['host'],
                'dbname'    => $this['config']['db']['dbname'],
                'user'      => $this['config']['db']['user'],
                'password'  => $this['config']['db']['password'],
            ),
        ));

        // https://github.com/tobiassjosten/FacebookServiceProvider
        $this->register(new FacebookServiceProvider(), array(
            'facebook.app_id' => $this['config']['facebook']['app_id'],
            'facebook.secret' => $this['config']['facebook']['secret'],
        ));

        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->setCache(__DIR__.'/../../tmp/cache/');
            $twig->addGlobal('base_url', $app['request']->getSchemeAndHttpHost().'/');
            $twig->addGlobal('cdn_url', $app['config']['cdn']);
            $twig->addGlobal('page', $app['page']);

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
    }
}

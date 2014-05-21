<?php

//require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use SilexAssetic\AsseticServiceProvider;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Response;

use DynImageServer\Manager\Provider\ManagerControllerProvider;
use DynImageServer\Manager\Provider\LoginControllerProvider;
use DynImageServer\Manager\Provider\PreviewDynImageControllerProvider;

use DynImageSilex\DynImageSilexServiceProvider;



//APP_ENV est défini dans le vhost apache
//$env = getenv('APP_ENV') ? : 'prod';

$app->register(new HttpCacheServiceProvider());

$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

//$app->register(new ServiceControllerServiceProvider());


$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'manager' => array(
            'pattern' => '^/manager',
            // 'http' => true,
            'form' => array(
                'login_path' => '/login/manager',
                //'username_parameter' => 'form[username]',
                //'password_parameter' => 'form[password]',
                'check_path' => '/manager/login_check',
            ),
            'logout' => array('logout_path' => '/manager/logout'),
            'anonymous' => false,
            'users' => $app['security.users'],
        ),
    ),
));

/** /
  $app['security.encoder.digest'] = $app->share(function ($app) {
  return new PlaintextPasswordEncoder();
  });
  /* */
$app->register(new TranslationServiceProvider());
$app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $translator->addResource('yaml', __DIR__ . '/../resources/locales/fr.yml', 'fr');

            return $translator;
        }));

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../resources/log/app.log',
    'monolog.name' => 'app',
    'monolog.level' => 0
));

$app->register(new TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true
    ),
    //'twig.form.templates' => array('form_div_layout.html.twig', 'common/form_div_layout.html.twig'),
    'twig.path' => array(__DIR__ . '/../resources/views')
));



//$app['twig.loader.filesystem']->addPath(__DIR__.'/../dyngallery/src/DynGallery/');

        
if ($app['debug'] && isset($app['cache.path'])) {
    $app->register(new ServiceControllerServiceProvider());
    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => $app['cache.path'] . '/profiler',
    ));
}

if (isset($app['assetic.enabled']) && $app['assetic.enabled']) {
    $app->register(new AsseticServiceProvider(), array(
        'assetic.options' => array(
            'debug' => $app['debug'],
            'auto_dump_assets' => $app['debug'],
        )
    ));
    /**/
    $app['assetic.filter_manager'] = $app->share(
            $app->extend('assetic.filter_manager', function ($fm, $app) {
                $fm->set('lessphp', new Assetic\Filter\LessphpFilter());

                return $fm;
            })
    );
    /**/
    $app['assetic.asset_manager'] = $app->share(
            $app->extend('assetic.asset_manager', function ($am, $app) {
                $am->set('styles', new Assetic\Asset\AssetCache(
                        new Assetic\Asset\GlobAsset(
                        $app['assetic.input.path_to_css'], array($app['assetic.filter_manager']->get('lessphp'))
                        ), new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
                ));
                $am->get('styles')->setTargetPath($app['assetic.output.path_to_css']);

                $am->set('scripts', new Assetic\Asset\AssetCache(
                        new Assetic\Asset\GlobAsset($app['assetic.input.path_to_js']), new Assetic\Cache\FilesystemCache($app['assetic.path_to_cache'])
                ));
                $am->get('scripts')->setTargetPath($app['assetic.output.path_to_js']);

                return $am;
            })
    );
}

/**/
//$app->register(new ModuleServiceProvider());
//$app->register(new PackagerServiceProvider());
/**/


//$app->mount('/' . $app['dyngallery']['routes_prefix'] . '/tympanus', new TympanusController());
//$app->mount('/' . $app['dyngallery']['routes_prefix'] . '/bootstrap', new BootstrapController());

/** /
$app->mount('/' . $app['dyngallery']['routes_prefix'] . '/tympanus', new Gallery\Themes\Tympanus\ControllerProvider());
$app->mount('/' . $app['dyngallery']['routes_prefix'] . '/bootstrap', new Gallery\Themes\Bootstrap\ControllerProvider());
/**/
//$app->mount('/' . $app['dynimage.routes_prefix'], new DynImageSilexControllerProvider());
//$app['dynimage.routes'] = array ('manager_preview' => '/preview_dynimage/{package}/{module}');
$app->register(new DynImageSilexServiceProvider());

$app->mount('/manager', new ManagerControllerProvider());
//$app->mount('/manager/dyngallery', new DynImageServer\Provider\GalleryControllerProvider());
$app->mount('/login/manager', new LoginControllerProvider());
$app->mount('/preview_dynimage', new PreviewDynImageControllerProvider());

$app->error(function (\Exception $e, $code) use ($app) {

    switch ($code) {

        case 404:
            $message = 'The requested page could not be found.';
            break;

        default:
            $message = 'We are sorry, but something went wrong.';

            if ($app['debug']) {
                $message = $e->getMessage();
            }
    }

    return new Response($message);
});

return $app;

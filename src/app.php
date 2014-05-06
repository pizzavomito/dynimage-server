<?php

use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use DynImageSilex\ModuleServiceProvider;
use DynImageSilex\PackagerServiceProvider;
use DynImageSilex\ControllerProvider;

use Symfony\Component\HttpFoundation\Response;

//APP_ENV est défini dans le vhost apache
$app['env'] = getenv('APP_ENV') ? : 'prod';

$app->register(new HttpCacheServiceProvider());

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../resources/log/app.log',
    'monolog.name'    => 'app',
    'monolog.level'   => 0
));


$app->register(new ModuleServiceProvider());
$app->register(new PackagerServiceProvider());

$app->mount('/'.$app['dynimage.routes_prefix'], new ControllerProvider());


$app->error(function (\Exception $e, $code) use ($app) {

            switch ($code) {

                case 404:
                    $message = 'The requested image could not be found.';
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

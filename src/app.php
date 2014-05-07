<?php
/** /
use DynImage\Filter\Resize;
use DynImage\Filter\Border;
use DynImage\Filter\Colorize;
use DynImage\Filter\Reflect;
use DynImage\DynImage;
use DynImage\Transformer;

$transformer = new Transformer();
$transformer->add(new Resize(array('height' => 200, 'width' => 200)));
$transformer->add(new Colorize(array('color' => '#ff9900')));
$transformer->add(new Border(array('height' => 6, 'width' => 6, 'color', '#000')));
$transformer->add(new Reflect());

$filename = 'http://cdn-parismatch.ladmedia.fr/var/news/storage/images/paris-match/actu/politique/dominique-strauss-kahn-a-accepte-de-se-soumettre-a-des-test-adn-149718/1455872-1-fre-FR/DSK-soumis-a-des-test-ADN_article_landscape_pm_v8.jpg';

$image = DynImage::getImage($transformer, file_get_contents($filename), $filename);

$image->show('png');
/**/

/**/
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;

use DynImageSilex\DynImageSilexServiceProvider;

use Symfony\Component\HttpFoundation\Response;

//APP_ENV est défini dans le vhost apache
//$app['env'] = getenv('APP_ENV') ? : 'prod';

$app->register(new HttpCacheServiceProvider());

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../resources/log/app.log',
    'monolog.name'    => 'app',
    'monolog.level'   => 0
));

$app->register(new DynImageSilexServiceProvider(), array(
    'dynimage.routes_depth'     => 10,
    'dynimage.cache_dir'        => __DIR__ . '/../resources/cache/dynimage/'
));





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
/**/
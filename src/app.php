<?php
/** /
use DynImage\Filter\Resize;
use DynImage\Filter\Border;
use DynImage\Filter\Colorize;
use DynImage\Filter\Reflect;
use DynImage\Filter\Experimental\WatermarkText;
use DynImage\DynImage;
use DynImage\Events;



$di = new DynImage();

$di->add(new Resize(array('height' => 200, 'width' => 200)));
//$di->add(new Colorize(array('color' => '#ff9900')));
//$di->add(new WatermarkText(array('text' => 'DSK', 'position' => 'center', 'font_size' => 150, 'font_color' => '#ffffff')),Events::FINISH_CREATE_IMAGE);
$di->add(new Border(array('height' => 6, 'width' => 6, 'color', '#000')));

//$di->add(new Reflect());

$filename = 'http://cdn-parismatch.ladmedia.fr/var/news/storage/images/paris-match/actu/politique/dominique-strauss-kahn-a-accepte-de-se-soumettre-a-des-test-adn-149718/1455872-1-fre-FR/DSK-soumis-a-des-test-ADN_article_landscape_pm_v8.jpg';

$image = $di->apply(file_get_contents($filename));
//$image = DynImage::getImage($transformer, file_get_contents($filename), $filename);

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

$app->register(new DynImageSilexServiceProvider());



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
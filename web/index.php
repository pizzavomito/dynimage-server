<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__.'/../resources/config/prod.php';
require __DIR__.'/../src/app.php';

if ($app['dynimage.cache']) {
    $app['http_cache']->run();
}
else {
    $app->run();
}
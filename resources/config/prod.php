<?php
$app['env'] = 'prod';
//define('APP_DIR', dirname(dirname(dirname(__FILE__))));
//define('ENV', $app['env']);

$app['debug'] = false;

$app['cache.path'] = __DIR__ . '/../cache';

$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

$app['dynimage.cache'] = false;


//$app['dynimage.packager_file'] = __DIR__ . '/../dynimage/packager.xml';
$app['dynimage.available_dir'] = __DIR__ . '/../../dynimage/packages-available';
$app['dynimage.routes_prefix'] = 'dynimage';
$app['dynimage.cache_dir'] = $app['cache.path'] . '/dynimage';
$app['dynimage.routes_depth'] = 10;
$app['dynimage.extensions'] = array( 'DynImageServer\Manager\Extension');


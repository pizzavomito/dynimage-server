<?php
$app['env'] = 'prod';
define('APP_DIR', dirname(dirname(dirname(__FILE__))));
define('ENV', $app['env']);

$app['debug'] = false;

$app['cache.path'] = __DIR__ . '/../cache';

$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

$app['dynimage.cache'] = false;
$app['dynimage.cache_dir'] = $app['cache.path'] . '/dynimage';
$app['dynimage.packager'] = __DIR__ . '/../dynimage/packager.xml';
$app['dynimage.routes_prefix'] = 'dynimage';
$app['dynimage.routes_depth'] = 10;

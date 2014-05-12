<?php

// include the prod configuration
require __DIR__.'/prod.php';




// Cache
$app['cache.path'] = __DIR__ . '/../cache';

$app['dynimage_manager.cache_dir'] = $app['cache.path'] . '/manager';

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';

// Twig cache
$app['twig.options.cache'] = $app['cache.path'] . '/twig';
// Local
$app['locale'] = 'fr';
$app['session.default_locale'] = $app['locale'];
$app['translator.messages'] = array(
    'fr' => __DIR__ . '/../resources/locales/fr.yml',
);

// Assetic
$app['assetic.enabled'] = true;
$app['assetic.path_to_cache'] = $app['cache.path'] . '/assetic';
$app['assetic.path_to_web'] = __DIR__ . '/../../web/assets';
$app['assetic.input.path_to_assets'] = __DIR__ . '/../assets';

$app['assetic.input.path_to_css'] = $app['assetic.input.path_to_assets'] . '/less/style.less';
$app['assetic.output.path_to_css'] = 'css/styles.css';
$app['assetic.input.path_to_js'] = array(
    //__DIR__.'/../../vendor/twitter/bootstrap/js/bootstrap-tooltip.js',
    //__DIR__.'/../../vendor/twitter/bootstrap/js/*.js',
    
    $app['assetic.input.path_to_assets'] . '/js/*.js',
);
$app['assetic.output.path_to_js'] = 'js/scripts.js';

// User
$app['security.users'] = array(
    'admin' => array(
        'ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='));


<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__.'/../resources/config/manager_prod.php';
require __DIR__.'/../src/manager.php';


$app->run();
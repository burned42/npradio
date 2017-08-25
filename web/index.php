<?php

use NPRadio\Controller\ApiController;
use NPRadio\Controller\IndexController;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

// TODO disable debugging eventually
$app['debug'] = true;

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views'
]);

$app->mount('/', new IndexController());

$app->mount('/api', new ApiController());

$app->run();

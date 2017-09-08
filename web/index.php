<?php

use NPRadio\Controller\ApiController;
use NPRadio\Controller\IndexController;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views'
]);

$app->register(new Silex\Provider\HttpCacheServiceProvider(), [
    'http_cache.cache_dir' => __DIR__.'/cache/'
]);

$app->mount('/', new IndexController());

$app->mount('/api', new ApiController());

$app['http_cache']->run();


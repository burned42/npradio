<?php

require_once __DIR__.'/../vendor/autoload.php';

use NPRadio\Controller\ApiController;
use NPRadio\Controller\IndexController;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

$container = new \Slim\Container();
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};
$container['view'] = function ($container) {
    $view = new Twig(__DIR__.'/../views');
    $view->addExtension(new TwigExtension($container['router'], ''));

    return $view;
};

$app = new Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 30));

$app->get('/', IndexController::class.':getIndex');
$app->get('/api/radios', ApiController::class.':getRadios');
$app->get('/api/radios/{radioName}/streams', ApiController::class.':getStreams');
$app->get('/api/radios/{radioName}/streams/{streamName}', ApiController::class.':getStreamInfo');

$app->run();

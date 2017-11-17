<?php

use NPRadio\Controller\ApiController;
use NPRadio\Controller\IndexController;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

require_once __DIR__ . '/../vendor/autoload.php';


$container = new \Slim\Container();
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};
$container['view'] = function ($container) {
    $view = new Twig(
        __DIR__ . '/../views',
        ['cache' => false] // TODO ???
    );

    $basePath = rtrim(
        str_ireplace(
            'index.php',
            '',
            $container['request']->getUri()->getBasePath()
        ),
        '/'
    );

    $view->addExtension(new TwigExtension($container['router'], $basePath));

    return $view;
};

$app = new Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 600));

$app->get('/', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html.twig');
});

$app->get('/api/radios', ApiController::class . ':getRadios');
$app->get('/api/radios/{radioName}/streams', ApiController::class . ':getStreams');
$app->get('/api/radios/{radioName}/streams/{streamName}', ApiController::class . ':getStreamInfo');

$app->run();


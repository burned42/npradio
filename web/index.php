<?php

use Silex\Application;

require_once '../vendor/autoload.php';

$app = new Application();

// TODO disable debugging eventually
$app['debug'] = true;

$app->mount('/api', new \NPRadio\Controller\ApiController());

$app->run();

<?php

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetaRadio;

require_once '../vendor/autoload.php';


$metaRadio = new MetaRadio(new HttpDomFetcher());

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/radios', function () use ($metaRadio) {
    // TODO return list of available radios
});
$app->get('/radios/{radioName}', function () use ($metaRadio) {
    // TODO can we return something usefull in this case?
});
$app->get('/radios/{radioName}/streams', function () use ($metaRadio) {
    // TODO return list of available streams for radio
});
$app->get('radios/{radioName}/streams/{streamName}', function (Silex\Application $app, $radioName, $streamName) use ($metaRadio) {
    $info = $metaRadio->getInfo($radioName, $streamName);

    return $app->json($info->getAsArray());
});

$app->run();

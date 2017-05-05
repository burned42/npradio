<?php

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetaRadio;
use Symfony\Component\HttpFoundation\Request;

require_once '../vendor/autoload.php';


$metaRadio = new MetaRadio(new HttpDomFetcher());

$app = new Silex\Application();
$app['debug'] = true;

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    $app->abort(500, $e->getMessage());  // TODO replace this with a pretty error message after testing
});

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

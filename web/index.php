<?php

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;
use Symfony\Component\HttpFoundation\Request;

require_once '../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

try {
    $radioContainer = new RadioContainer();
    $domFetcher = new HttpDomFetcher();
    $radioStreams = [
        MetalOnly::class,
        RauteMusik::class,
        TechnoBase::class
    ];
    foreach ($radioStreams as $radioStream) {
        $radioContainer->addRadio(new $radioStream($domFetcher));
    }
} catch (Exception $e) {
    $app->abort(500, $e->getMessage()); // TODO replace this with a pretty error message after testing
}

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    $app->abort(500, $e->getMessage());  // TODO replace this with a pretty error message after testing
});

$app->get('/radios', function () use ($radioContainer) {
    // TODO return list of available radios
});
$app->get('/radios/{radioName}', function () use ($radioContainer) {
    // TODO can we return something usefull in this case?
});
$app->get('/radios/{radioName}/streams', function () use ($radioContainer) {
    // TODO return list of available streams for radio
});
$app->get('radios/{radioName}/streams/{streamName}', function (Silex\Application $app, $radioName, $streamName) use ($radioContainer) {
    $info = $radioContainer->getInfo($radioName, $streamName);

    return $app->json($info->getAsArray());
});

$app->run();

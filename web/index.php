<?php

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;
use Silex\Application;

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
} catch (\Exception $e) {
    $app->abort(500, $e->getMessage());
}

$app->get('/radios', function (Application $app) use ($radioContainer) {
    return $app->json($radioContainer->getRadioNames());
});
$app->get('/radios/{radioName}/streams', function (Application $app, string $radioName) use ($radioContainer) {
    return $app->json(
        $radioContainer->getStreamNames($radioName)
    );
});
$app->get(
    'radios/{radioName}/streams/{streamName}',
    function (Application $app, string $radioName, string $streamName) use ($radioContainer) {
        return $app->json(
            $radioContainer->getInfo($radioName, $streamName)->getAsArray()
        );
    }
);

$app->run();

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
    $app->abort(500, $e->getMessage()); // TODO replace this with a pretty error message after testing
}

$app->get('/radios', function (Application $app) use ($radioContainer) {
    return $app->json($radioContainer->getRadioNames());
});
$app->get('/radios/{radioName}', function () use ($radioContainer) {
    // TODO can we return something usefull in this case?
});
$app->get('/radios/{radioName}/streams', function () use ($radioContainer) {
    // TODO return list of available streams for radio
});
$app->get(
    'radios/{radioName}/streams/{streamName}',
    function (Application $app, $radioName, $streamName) use ($radioContainer) {
        return $app->json(
            $radioContainer->getInfo($radioName, $streamName)->getAsArray()
        );
    }
);

$app->run();

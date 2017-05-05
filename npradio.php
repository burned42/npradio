<?php

require_once 'vendor/autoload.php';

use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\RadioStream;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;


$radioStreams = [
    MetalOnly::class => [
        MetalOnly::METAL_ONLY
    ],
    RauteMusik::class => [
        RauteMusik::MAIN,
        RauteMusik::CLUB
    ],
    TechnoBase::class => [
        TechnoBase::TECHNOBASE,
        TechnoBase::HOUSETIME
    ]
];

$domFetcher = new HttpDomFetcher();
$radioContainer = new RadioContainer();
/** @var RadioStream $radioStream */
foreach ($radioStreams as $radioStream => $streams) {
    /** @var RadioStream $stream */
    $stream = new $radioStream($domFetcher);
    $radioName = $stream->getRadioName();
    $radioContainer->addRadio($stream);

    foreach ($streams as $streamName) {
        echo $streamName . "\n";
        try {
            var_dump($radioContainer->getInfo($radioName, $streamName));
        } catch (\Exception $e) {
            echo 'could not get info from ' . $streamName . ': ' . $e->getMessage() . "\n";
        }
    }
}

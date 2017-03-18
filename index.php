<?php

require_once 'vendor/autoload.php';

use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\MetaRadio;
use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;


$radioStreams = [
    MetalOnly::RADIO_NAME => [
        MetalOnly::METAL_ONLY
    ],
    TechnoBase::RADIO_NAME => [
        TechnoBase::TECHNOBASE,
        TechnoBase::HOUSETIME
    ],
    RauteMusik::RADIO_NAME => [
        RauteMusik::MAIN
    ]
];

$domFetcher = new HttpDomFetcher();
$metaRadio = new MetaRadio($domFetcher);

foreach ($radioStreams as $radioName => $streams) {
    foreach ($streams as $streamName) {
        echo $streamName . "\n";
        try {
            var_dump($metaRadio->getInfo($radioName, $streamName));
        } catch (\Exception $e) {
            echo 'could not get info from ' . $streamName . ': ' . $e->getMessage() . "\n";
        }
    }
}

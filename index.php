<?php

require_once 'vendor/autoload.php';

use NPRadio\Stream\{
    MetalOnly, MetaRadio, RauteMusik, TechnoBase
};
use NPRadio\DataFetcher\HttpDomFetcher;


$radioStreams = [
    MetalOnly::METAL_ONLY,
    TechnoBase::TECHNOBASE,
    TechnoBase::HOUSETIME,
    RauteMusik::MAIN
];

$domFetcher = new HttpDomFetcher();
$metaRadio = new MetaRadio($domFetcher);

foreach ($radioStreams as $streamName) {
    echo $streamName . "\n";
    try {
        var_dump($metaRadio->getInfo($streamName));
    } catch (\Exception $e) {
        echo 'could not get info from ' . $streamName . ': ' . $e->getMessage() . "\n";
    }
}

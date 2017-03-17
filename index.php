<?php

namespace NPRadio;

require_once 'vendor/autoload.php';

use NPRadio\Stream\{RadioStream, MetalOnly, RauteMusik, TechnoBase};
use NPRadio\DataFetcher\HttpDomFetcher;

$domFetcher = new HttpDomFetcher();

$streams = [
    MetalOnly::RADIO_NAME  => new MetalOnly($domFetcher),
    TechnoBase::TECHNOBASE => new TechnoBase($domFetcher, TechnoBase::TECHNOBASE),
    TechnoBase::HOUSETIME  => new TechnoBase($domFetcher, TechnoBase::HOUSETIME),
    RauteMusik::MAIN       => new RauteMusik($domFetcher, RauteMusik::MAIN)
];

/** @var RadioStream $stream */
foreach ($streams as $name => $stream) {
    try {
        echo $name . "\n";
        var_dump($stream->getInfo());
    } catch (\Exception $e) {
        echo 'could not get info from ' . $name . ': ' . $e->getMessage() . "\n";
    }
}

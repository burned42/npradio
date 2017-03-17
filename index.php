<?php

require_once 'vendor/autoload.php';

use NPRadio\Stream\{
    RadioStream, MetalOnly, RauteMusik, TechnoBase
};
use NPRadio\DataFetcher\HttpDomFetcher;

$domFetcher = new HttpDomFetcher();

/** @var RadioStream[] $radios */
$radios = [
    MetalOnly::RADIO_NAME  => new MetalOnly($domFetcher),
    TechnoBase::RADIO_NAME => new TechnoBase($domFetcher),
    RauteMusik::RADIO_NAME => new RauteMusik($domFetcher)
];

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

/** @var RadioStream $stream */
foreach ($radioStreams as $radioName => $streams) {
    foreach ($streams as $stream) {
        echo $stream . "\n";
        try {
            var_dump($radios[$radioName]->getInfo($stream));
        } catch (\Exception $e) {
            echo 'could not get info from ' . $stream . ': ' . $e->getMessage() . "\n";
        }
    }
}

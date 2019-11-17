#!/usr/bin/php
<?php

require_once 'vendor/autoload.php';

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use App\Stream\MetalOnly;
use App\Stream\RadioGalaxy;
use App\Stream\RauteMusik;
use App\Stream\SlayRadio;
use App\Stream\StarFm;
use App\Stream\TechnoBase;

$radioStreams = [
    MetalOnly::class,
    RadioGalaxy::class,
    RauteMusik::class,
    SlayRadio::class,
    StarFm::class,
    TechnoBase::class,
];

$availableRadios = [];
foreach ($radioStreams as $radioClass) {
    $availableRadios[$radioClass::getRadioName()] = $radioClass;
}

$radios = [];
if (1 !== $argc) {
    if ($argc > 3) {
        echo "too many arguments\n";
        exit;
    }

    if ($argc > 1) {
        if (!array_key_exists($argv[1], $availableRadios)) {
            echo "invalid radio name given\n";
            exit;
        }

        $streams = $availableRadios[$argv[1]]::getAvailableStreams();

        if ($argc > 2) {
            if (!in_array($argv[2], $streams, true)) {
                echo "invalid stream name given\n";
                exit;
            }

            $radios[$argv[1]] = [$argv[2]];
        } else {
            $radios[$argv[1]] = $streams;
        }
    }
} else {
    foreach ($availableRadios as $name => $radioClass) {
        $radios[$name] = $radioClass::getAvailableStreams();
    }
}

$domFetcher = new HttpDomFetcher();

try {
    foreach ($radios as $radioName => $streams) {
        echo $radioName.":\n";
        /** @var array $streams */
        foreach ($streams as $streamName) {
            echo '    '.$streamName.":\n";

            /** @var AbstractRadioStream $radioStream */
            $radioStream = new $availableRadios[$radioName]($domFetcher, $streamName);

            if (null !== $radioStream->getShow()) {
                echo '        Show:      '.$radioStream->getShow()."\n";
            }
            if (null !== $radioStream->getGenre()) {
                echo '        Genre:     '.$radioStream->getGenre()."\n";
            }
            if (null !== $radioStream->getModerator()) {
                echo '        Moderator: '.$radioStream->getModerator()."\n";
            }
            if (
                $radioStream->getShowStartTime() instanceof DateTime
                && $radioStream->getShowEndTime() instanceof DateTime
            ) {
                echo '        Showtime:  '.$radioStream->getShowStartTime()->format('H:i')
                    .' - '.$radioStream->getShowEndTime()->format('H:i')."\n";
            }
            echo '        Track:     ';
            $artist = $radioStream->getArtist();
            $track = $radioStream->getTrack();
            if (null !== $artist || null !== $track) {
                if (null !== $artist) {
                    echo $artist;
                }
                if (null !== $artist && null !== $track) {
                    echo ' - ';
                }
                if (null !== $track) {
                    echo $track;
                }
            } else {
                echo 'n/a';
            }
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo 'got exception: '.$e->getMessage();
}

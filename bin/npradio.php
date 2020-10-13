#!/usr/bin/php
<?php

declare(strict_types=1);

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

/** @var AbstractRadioStream[] $availableRadios */
$availableRadios = [];
$httpDomFetcher = new HttpDomFetcher();
/** @var AbstractRadioStream $radioClass */
foreach ($radioStreams as $radioClass) {
    $availableRadios[$radioClass::getRadioName()] = new $radioClass($httpDomFetcher);
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

        $streams = $availableRadios[$argv[1]]->getAvailableStreams();

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
    foreach ($availableRadios as $name => $radio) {
        $radios[$name] = $radio->getAvailableStreams();
    }
}

$domFetcher = new HttpDomFetcher();

try {
    foreach ($radios as $radioName => $streams) {
        echo $radioName.":\n";
        $radioStream = $availableRadios[$radioName];
        /** @var array $streams */
        foreach ($streams as $streamName) {
            echo '    '.$streamName.":\n";
            $streamInfo = $radioStream->getStreamInfo($streamName);

            if (null !== $streamInfo->show) {
                echo '        Show:      '.$streamInfo->show."\n";
            }
            if (null !== $streamInfo->genre) {
                echo '        Genre:     '.$streamInfo->genre."\n";
            }
            if (null !== $streamInfo->moderator) {
                echo '        Moderator: '.$streamInfo->moderator."\n";
            }
            if (
                $streamInfo->showStartTime instanceof DateTimeInterface
                && $streamInfo->showEndTime instanceof DateTimeInterface
            ) {
                echo '        Showtime:  '.$streamInfo->showStartTime->format('H:i')
                    .' - '.$streamInfo->showEndTime->format('H:i')."\n";
            }
            echo '        Track:     ';
            $artist = $streamInfo->artist;
            $track = $streamInfo->track;
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

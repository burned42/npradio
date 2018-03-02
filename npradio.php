#!/usr/bin/php
<?php

require_once 'vendor/autoload.php';

use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\StarFm;
use NPRadio\Stream\TechnoBase;

try {
    $radioContainer = new RadioContainer();
    $domFetcher = new HttpDomFetcher();
    $radioStreams = [
        StarFm::class,
        MetalOnly::class,
        RauteMusik::class,
        TechnoBase::class,
    ];
    foreach ($radioStreams as $radioStream) {
        $radioContainer->addRadio(new $radioStream($domFetcher));
    }
} catch (Exception $e) {
    echo "could not instantiate radio container\n";
    exit;
}

$radios = [];
if (1 !== $argc) {
    if ($argc > 3) {
        echo "too many arguments\n";
        exit;
    }
    if ($argc > 2) {
        if (false === $radioContainer->containsStream($argv[1], $argv[2])) {
            echo "invalid stream name given\n";
            exit;
        }
        $radios[$argv[1]] = [$argv[2]];
    } elseif ($argc > 1) {
        if (false === $radioContainer->containsRadio($argv[1])) {
            echo "invalid radio name given\n";
            exit;
        }

        $radios[$argv[1]] = $radioContainer->getStreamNames($argv[1]);
    }
} else {
    foreach ($radioContainer->getRadioNames() as $key => $radio) {
        $radios[$radio] = $radioContainer->getStreamNames($radio);
    }
}

try {
    foreach ($radios as $radioName => $streams) {
        echo $radioName.":\n";
        /** @var array $streams */
        foreach ($streams as $streamName) {
            echo '    '.$streamName.":\n";
            $streamInfo = $radioContainer->getInfo($radioName, $streamName);

            if (null !== $streamInfo->getShow()) {
                echo '        Show:      '.$streamInfo->getShow()."\n";
            }
            if (null !== $streamInfo->getGenre()) {
                echo '        Genre:     '.$streamInfo->getGenre()."\n";
            }
            if (null !== $streamInfo->getModerator()) {
                echo '        Moderator: '.$streamInfo->getModerator()."\n";
            }
            if (
                $streamInfo->getShowStartTime() instanceof DateTime
                && $streamInfo->getShowEndTime() instanceof DateTime
            ) {
                echo '        Showtime:  '.$streamInfo->getShowStartTime()->format('H:i')
                    .' - '.$streamInfo->getShowEndTime()->format('H:i')."\n";
            }
            echo '        Track:     ';
            $artist = $streamInfo->getArtist();
            $track = $streamInfo->getTrack();
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

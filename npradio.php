#!/usr/bin/php
<?php

require_once 'vendor/autoload.php';

use NPRadio\Stream\MetalOnly;
use NPRadio\Stream\RadioContainer;
use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\RauteMusik;
use NPRadio\Stream\TechnoBase;


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
} catch (Exception $e) {
    echo "could not instantiate radio container\n";
    exit;
}

$radios = [];
if ($argc !== 1) {
    if ($argc > 3) {
        echo "too many arguments\n";
        exit;
    }
    if ($argc > 2) {
        if ($radioContainer->containsStream($argv[1], $argv[2]) === false) {
            echo "invalid stream name given\n";
            exit;
        }
        $radios[$argv[1]] = [$argv[2]];
    } elseif ($argc > 1) {
        if ($radioContainer->containsRadio($argv[1]) === false) {
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
        echo $radioName . ":\n";
        foreach ($streams as $streamName) {
            echo "    " . $streamName . ":\n";
            $streamInfo = $radioContainer->getInfo($radioName, $streamName);

            if (!is_null($streamInfo->getShow())) {
                echo "        Show:      " . $streamInfo->getShow() . "\n";
            }
            if (!is_null($streamInfo->getGenre())) {
                echo "        Genre:     " . $streamInfo->getGenre() . "\n";
            }
            if (!is_null($streamInfo->getModerator())) {
                echo "        Moderator: " . $streamInfo->getModerator() . "\n";
            }
            if (
                $streamInfo->getShowStartTime() instanceof DateTime
                && $streamInfo->getShowEndTime() instanceof DateTime
            ) {
                echo "        Showtime:  " . $streamInfo->getShowStartTime()->format('H:i')
                    . ' - ' . $streamInfo->getShowEndTime()->format('H:i') . "\n";
            }
            echo "        Track:     ";
            if (!is_null($streamInfo->getArtist()) || !is_null($streamInfo->getTrack())) {
                if (!is_null($streamInfo->getArtist())) {
                    echo $streamInfo->getArtist();
                }
                if (!is_null($streamInfo->getArtist()) && !is_null($streamInfo->getTrack())) {
                    echo ' - ';
                }
                if (!is_null($streamInfo->getTrack())) {
                    echo $streamInfo->getTrack();
                }
            } else {
                echo 'n/a';
            }
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo 'got exception: ' . $e->getMessage();
}

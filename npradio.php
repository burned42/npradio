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

    foreach ($radioContainer->getRadioNames() as $radioName) {
        echo $radioName . ":\n";
        foreach ($radioContainer->getStreamNames($radioName) as $streamName) {
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
            if (!is_null($streamInfo->getTrack())) {
                echo $streamInfo->getTrack();
                if (!is_null($streamInfo->getArtist())) {
                    echo ' - ' . $streamInfo->getArtist();
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

<?php

namespace Burned\NPRadio;

require_once 'RadioStreamInterface.php';
require_once 'RadioInfo.php';

class RauteMusik implements RadioStreamInterface
{
    const BASE_URL = 'http://www.rautemusik.fm/';

    const MAIN = 'Main';
    const CLUB = 'Club';
    const HOUSE = 'House';
    const WACKENRADIO = 'WackenRadio';
    const METAL = 'Metal;';

    const AVAILABLE_STREAMS = [
        self::MAIN,
        self::CLUB,
        self::HOUSE,
        self::WACKENRADIO,
        self::METAL
    ];

    private $streamName;
    private $radioInfo;

    public function __construct($streamName)
    {
        if (!in_array($streamName, self::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given');
        }

        $this->streamName = $streamName;

        $this->radioInfo = new RadioInfo();
        $this->radioInfo->setStreamName('#Musik.' . $this->streamName);
    }

    public function getInfo(): RadioInfo
    {
        return $this->radioInfo;
    }
}
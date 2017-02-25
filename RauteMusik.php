<?php

namespace Burned\NPRadio;

require_once 'RadioStreamInterface.php';
require_once 'RadioInfo.php';

class RauteMusik implements RadioStreamInterface
{
    const BASE_URL = 'http://www.rautemusik.fm/';

    private $radioInfo;

    public function __construct()
    {
        $this->radioInfo = new RadioInfo();
        $this->radioInfo->setStreamName('#Musik.xyz');
    }

    private function fetchInfo()
    {

    }

    public function getInfo(): RadioInfo
    {
        $this->fetchInfo();

        return $this->radioInfo;
    }
}
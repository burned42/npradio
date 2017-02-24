<?php

namespace Burned\NPRadio;

class RauteMusik implements RadioStreamInterface
{
    const BASE_URL = 'http://www.rautemusik.fm/';

    private function fetchInfo()
    {

    }

    public function getInfo(): array
    {
        $this->fetchInfo();

        return [

        ];
    }
}
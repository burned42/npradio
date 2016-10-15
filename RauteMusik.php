<?php
/**
 * Created by PhpStorm.
 * User: burned
 * Date: 15.10.16
 * Time: 20:27
 */

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
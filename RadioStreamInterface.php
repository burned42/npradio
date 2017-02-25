<?php

namespace Burned\NPRadio;

require_once 'RadioInfo.php';

interface RadioStreamInterface
{
    public function getInfo(): RadioInfo;
}

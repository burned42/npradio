<?php

namespace NPRadio\Stream;

interface RadioStreamInterface
{
    public function getInfo(string $streamName): StreamInfo;
}

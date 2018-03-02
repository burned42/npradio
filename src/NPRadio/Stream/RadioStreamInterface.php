<?php

declare(strict_types=1);

namespace NPRadio\Stream;

interface RadioStreamInterface
{
    public function getInfo(string $streamName): StreamInfo;
}

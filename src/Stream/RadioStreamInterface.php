<?php

declare(strict_types=1);

namespace NPRadio\Stream;

interface RadioStreamInterface
{
    /**
     * @param string $streamName
     *
     * @return StreamInfo
     */
    public function getInfo(string $streamName): StreamInfo;
}

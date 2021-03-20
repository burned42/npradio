<?php

declare(strict_types=1);

namespace App\Stream;

use App\DataFetcher\HttpDataFetcherInterface;

abstract class AbstractRadioStream
{
    public function __construct(private HttpDataFetcherInterface $httpDataFetcher)
    {
    }

    protected function getHttpDataFetcher(): HttpDataFetcherInterface
    {
        return $this->httpDataFetcher;
    }

    abstract public static function getRadioName(): string;

    /**
     * @return array<string>
     */
    abstract public function getAvailableStreams(): array;

    abstract public function getStreamInfo(string $streamName): StreamInfo;
}

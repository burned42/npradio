<?php

declare(strict_types=1);

namespace App\Stream;

use App\DataFetcher\DomFetcherInterface;

abstract class AbstractRadioStream
{
    private DomFetcherInterface $domFetcher;

    public function __construct(DomFetcherInterface $domFetcher)
    {
        $this->domFetcher = $domFetcher;
    }

    protected function getDomFetcher(): DomFetcherInterface
    {
        return $this->domFetcher;
    }

    abstract public static function getRadioName(): string;

    /**
     * @return array<string>
     */
    abstract public function getAvailableStreams(): array;

    abstract public function getStreamInfo(string $streamName): StreamInfo;
}

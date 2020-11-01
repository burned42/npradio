<?php

declare(strict_types=1);

namespace App\Stream;

use App\DataFetcher\DomFetcherInterface;
use DateTimeInterface;

abstract class AbstractRadioStream
{
    private DomFetcherInterface $domFetcher;

    protected ?string $track = null;
    protected ?string $artist = null;
    protected ?string $show = null;
    protected ?string $genre = null;
    protected ?string $moderator = null;
    protected ?DateTimeInterface $showStartTime = null;
    protected ?DateTimeInterface $showEndTime = null;

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

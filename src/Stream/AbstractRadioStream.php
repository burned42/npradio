<?php

declare(strict_types=1);

namespace App\Stream;

use App\DataFetcher\HttpDomFetcher;
use DateTime;
use InvalidArgumentException;

abstract class AbstractRadioStream
{
    private HttpDomFetcher $domFetcher;
    private string $streamName;

    private ?string $track = null;
    private ?string $artist = null;
    private ?string $show = null;
    private ?string $genre = null;
    private ?string $moderator = null;
    private ?DateTime $showStartTime = null;
    private ?DateTime $showEndTime = null;

    public function __construct(HttpDomFetcher $domFetcher, string $streamName)
    {
        if (!in_array($streamName, static::getAvailableStreams(), true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        $this->domFetcher = $domFetcher;
        $this->streamName = $streamName;

        $this->updateInfo();
    }

    protected function getDomFetcher(): HttpDomFetcher
    {
        return $this->domFetcher;
    }

    abstract public function updateInfo(): void;

    abstract public static function getRadioName(): string;

    /**
     * @return array<string>
     */
    abstract public static function getAvailableStreams(): array;

    public function getStreamName(): string
    {
        return $this->streamName;
    }

    abstract protected function getHomepageUrl(): string;

    abstract protected function getStreamUrl(): string;

    /**
     * @return array<string, string|array<string|null>|null>
     */
    public function getAsArray(): array
    {
        $showStartTime = $this->getShowStartTime();
        if (null !== $showStartTime) {
            $showStartTime = $showStartTime->format('H:i');
        }

        $showEndTime = $this->getShowEndTime();
        if (null !== $showEndTime) {
            $showEndTime = $showEndTime->format('H:i');
        }

        return [
            'radio_name' => static::getRadioName(),
            'stream_name' => $this->getStreamName(),
            'homepage' => $this->getHomepageUrl(),
            'stream_url' => $this->getStreamUrl(),
            'show' => [
                'name' => $this->getShow(),
                'genre' => $this->getGenre(),
                'moderator' => $this->getModerator(),
                'start_time' => $showStartTime,
                'end_time' => $showEndTime,
            ],
            'track' => $this->getTrack(),
            'artist' => $this->getArtist(),
        ];
    }

    public function getShow(): ?string
    {
        return $this->show;
    }

    public function setShow(string $show = null): self
    {
        $this->show = $show;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre = null): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getModerator(): ?string
    {
        return $this->moderator;
    }

    public function setModerator(string $moderator = null): self
    {
        $this->moderator = $moderator;

        return $this;
    }

    public function getShowStartTime(): ?DateTime
    {
        return $this->showStartTime;
    }

    public function setShowStartTime(DateTime $showStartTime = null): self
    {
        $this->showStartTime = $showStartTime;

        return $this;
    }

    public function getShowEndTime(): ?DateTime
    {
        return $this->showEndTime;
    }

    public function setShowEndTime(DateTime $showEndTime = null): self
    {
        $this->showEndTime = $showEndTime;

        return $this;
    }

    public function getTrack(): ?string
    {
        return $this->track;
    }

    public function setTrack(string $track = null): self
    {
        $this->track = $track;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist = null): self
    {
        $this->artist = $artist;

        return $this;
    }
}

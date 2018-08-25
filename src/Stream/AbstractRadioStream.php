<?php

declare(strict_types=1);

namespace App\Stream;

use App\DataFetcher\HttpDomFetcher;

abstract class AbstractRadioStream
{
    /** @var HttpDomFetcher */
    private $domFetcher;

    /** @var string */
    private $streamName;

    /** @var string */
    private $show;
    /** @var string */
    private $genre;
    /** @var string */
    private $moderator;
    /** @var \DateTime */
    private $showStartTime;
    /** @var \DateTime */
    private $showEndTime;

    /** @var string */
    private $track;
    /** @var string */
    private $artist;

    public function __construct(HttpDomFetcher $domFetcher, string $streamName)
    {
        if (!\in_array($streamName, static::getAvailableStreams(), true)) {
            throw new \InvalidArgumentException('Invalid stream name given');
        }

        $this->domFetcher = $domFetcher;
        $this->streamName = $streamName;

        $this->updateInfo();
    }

    protected function getDomFetcher(): HttpDomFetcher
    {
        return $this->domFetcher;
    }

    abstract public function updateInfo();

    abstract public static function getRadioName(): string;

    abstract public static function getAvailableStreams(): array;

    public function getStreamName(): string
    {
        return $this->streamName;
    }

    abstract protected function getHomepageUrl(): string;

    abstract protected function getStreamUrl(): string;

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

    /**
     * @return null|string
     */
    public function getShow()
    {
        return $this->show;
    }

    public function setShow(string $show = null): self
    {
        $this->show = $show;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    public function setGenre(string $genre = null): self
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    public function setModerator(string $moderator = null): self
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getShowStartTime()
    {
        return $this->showStartTime;
    }

    public function setShowStartTime(\DateTime $showStartTime = null): self
    {
        $this->showStartTime = $showStartTime;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getShowEndTime()
    {
        return $this->showEndTime;
    }

    public function setShowEndTime(\DateTime $showEndTime = null): self
    {
        $this->showEndTime = $showEndTime;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTrack()
    {
        return $this->track;
    }

    public function setTrack(string $track = null): self
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    public function setArtist(string $artist = null): self
    {
        $this->artist = $artist;

        return $this;
    }
}

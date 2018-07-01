<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use NPRadio\DataFetcher\HttpDomFetcher;

abstract class AbstractRadioStream
{
    /** @var HttpDomFetcher */
    protected $domFetcher;

    /** @var string */
    protected $streamName;

    /** @var string */
    protected $show;
    /** @var string */
    protected $genre;
    /** @var string */
    protected $moderator;
    /** @var \DateTime */
    protected $showStartTime;
    /** @var \DateTime */
    protected $showEndTime;

    /** @var string */
    protected $track;
    /** @var string */
    protected $artist;

    public function __construct(HttpDomFetcher $domFetcher, string $streamName)
    {
        if (!\in_array($streamName, static::getAvailableStreams(), true)) {
            throw new \InvalidArgumentException('Invalid stream name given');
        }

        $this->domFetcher = $domFetcher;
        $this->streamName = $streamName;

        $this->updateInfo();
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
            'radio_name' => $this->getRadioName(),
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

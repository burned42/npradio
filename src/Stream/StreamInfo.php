<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class StreamInfo
{
    /** @var string */
    protected $radioName;

    /** @var string */
    protected $streamName;
    /** @var string */
    protected $homepageUrl;
    /** @var string */
    protected $streamUrl;

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

    /**
     * @return array
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
     * @return string
     */
    public function getRadioName(): string
    {
        return $this->radioName;
    }

    /**
     * @param string $radioName
     *
     * @return StreamInfo
     */
    public function setRadioName(string $radioName = null): StreamInfo
    {
        $this->radioName = $radioName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreamName(): string
    {
        return $this->streamName;
    }

    /**
     * @param string $streamName
     *
     * @return StreamInfo
     */
    public function setStreamName(string $streamName = null): StreamInfo
    {
        $this->streamName = $streamName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreamUrl(): string
    {
        return $this->streamUrl;
    }

    /**
     * @param string $streamUrl
     *
     * @return StreamInfo
     */
    public function setStreamUrl(string $streamUrl = null): StreamInfo
    {
        $this->streamUrl = $streamUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getHomepageUrl(): string
    {
        return $this->homepageUrl;
    }

    /**
     * @param string $homepageUrl
     *
     * @return StreamInfo
     */
    public function setHomepageUrl(string $homepageUrl = null): StreamInfo
    {
        $this->homepageUrl = $homepageUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param string $show
     *
     * @return StreamInfo
     */
    public function setShow(string $show = null): StreamInfo
    {
        $this->show = $show;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     *
     * @return StreamInfo
     */
    public function setGenre(string $genre = null): StreamInfo
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * @param string $moderator
     *
     * @return StreamInfo
     */
    public function setModerator(string $moderator = null): StreamInfo
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

    /**
     * @param \DateTime $showStartTime
     *
     * @return StreamInfo
     */
    public function setShowStartTime(\DateTime $showStartTime = null): StreamInfo
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

    /**
     * @param \DateTime $showEndTime
     *
     * @return StreamInfo
     */
    public function setShowEndTime(\DateTime $showEndTime = null): StreamInfo
    {
        $this->showEndTime = $showEndTime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @param string $track
     *
     * @return StreamInfo
     */
    public function setTrack(string $track = null): StreamInfo
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     *
     * @return StreamInfo
     */
    public function setArtist(string $artist = null): StreamInfo
    {
        $this->artist = $artist;

        return $this;
    }
}
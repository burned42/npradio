<?php

namespace NPRadio\Stream;

class RadioInfo
{
    /** @var string */
    protected $track = null;
    /** @var string */
    protected $artist = null;

    /** @var string */
    protected $show = null;
    /** @var string */
    protected $moderator = null;
    /** @var \DateTime */
    protected $showStartTime = null;
    /** @var \DateTime */
    protected $showEndTime = null;

    /** @var string */
    protected $radioName = null;
    /** @var string */
    protected $homepageUrl = null;

    /** @var string */
    protected $streamName = null;
    /** @var string */
    protected $genre = null;

    /**
     * @return string
     */
    public function getHomepageUrl(): string
    {
        return $this->homepageUrl;
    }

    /**
     * @param string $homepageUrl
     * @return RadioInfo
     */
    public function setHomepageUrl(string $homepageUrl): RadioInfo
    {
        $this->homepageUrl = $homepageUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRadioName(): string
    {
        return $this->radioName;
    }

    /**
     * @param mixed $radioName
     * @return RadioInfo
     */
    public function setRadioName($radioName): RadioInfo
    {
        $this->radioName = $radioName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrack(): string
    {
        return $this->track;
    }

    /**
     * @param string $track
     * @return RadioInfo
     */
    public function setTrack(string $track): RadioInfo
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     * @return RadioInfo
     */
    public function setArtist(string $artist): RadioInfo
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return string
     */
    public function getShow(): string
    {
        return $this->show;
    }

    /**
     * @param string $show
     * @return RadioInfo
     */
    public function setShow(string $show): RadioInfo
    {
        $this->show = $show;

        return $this;
    }

    /**
     * @return string
     */
    public function getModerator(): string
    {
        return $this->moderator;
    }

    /**
     * @param string $moderator
     * @return RadioInfo
     */
    public function setModerator(string $moderator): RadioInfo
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getShowStartTime(): \DateTime
    {
        return $this->showStartTime;
    }

    /**
     * @param \DateTime $showStartTime
     * @return RadioInfo
     */
    public function setShowStartTime(\DateTime $showStartTime): RadioInfo
    {
        $this->showStartTime = $showStartTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getShowEndTime(): \DateTime
    {
        return $this->showEndTime;
    }

    /**
     * @param \DateTime $showEndTime
     * @return RadioInfo
     */
    public function setShowEndTime(\DateTime $showEndTime): RadioInfo
    {
        $this->showEndTime = $showEndTime;

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
     * @return RadioInfo
     */
    public function setStreamName(string $streamName): RadioInfo
    {
        $this->streamName = $streamName;

        return $this;
    }

    /**
     * @return string
     */
    public function getGenre(): string
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     * @return RadioInfo
     */
    public function setGenre(string $genre): RadioInfo
    {
        $this->genre = $genre;

        return $this;
    }
}
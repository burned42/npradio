<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use NPRadio\DataFetcher\DomFetcher;

abstract class RadioStream implements RadioStreamInterface
{
    protected $streamInfos = [];
    protected $domFetcher;

    /**
     * @var string
     */
    const RADIO_NAME = null;
    const AVAILABLE_STREAMS = [];

    /**
     * RadioStream constructor.
     *
     * @param DomFetcher $domFetcher
     */
    public function __construct(DomFetcher $domFetcher)
    {
        $this->domFetcher = $domFetcher;

        foreach (static::AVAILABLE_STREAMS as $streamName) {
            $streamInfo = new StreamInfo();
            $streamInfo->setRadioName($this->getRadioName())
                ->setStreamName($streamName)
                ->setHomepageUrl($this->getHomepageUrl($streamName))
                ->setStreamUrl($this->getStreamUrl($streamName));
            $this->streamInfos[$streamName] = $streamInfo;
        }
    }

    /**
     * @return string
     */
    public function getRadioName(): string
    {
        return static::RADIO_NAME;
    }

    /**
     * @return array
     */
    public function getStreamNames(): array
    {
        return static::AVAILABLE_STREAMS;
    }

    /**
     * @param string $streamName
     *
     * @return StreamInfo
     *
     * @throws \InvalidArgumentException
     */
    protected function getStreamInfo(string $streamName): StreamInfo
    {
        if (!array_key_exists($streamName, $this->streamInfos)) {
            throw new \InvalidArgumentException('no radio info object created for stream: '.$streamName);
        }

        return $this->streamInfos[$streamName];
    }

    /**
     * @param string $streamName
     *
     * @return string
     */
    abstract protected function getHomepageUrl(string $streamName): string;

    /**
     * @param string $streamName
     *
     * @return string
     */
    abstract protected function getStreamUrl(string $streamName): string;
}

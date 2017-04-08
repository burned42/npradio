<?php

namespace NPRadio\Stream;

use NPRadio\DataFetcher\DomFetcher;

abstract class RadioStream implements RadioStreamInterface
{
    protected $streamInfos;
    protected $domFetcher;

    const RADIO_NAME = null;
    const AVAILABLE_STREAMS = [];

    public function __construct(DomFetcher $domFetcher)
    {
        $this->domFetcher = $domFetcher;

        foreach (static::AVAILABLE_STREAMS as $streamName) {
            $streamInfo = new StreamInfo();
            $streamInfo->setRadioName(static::RADIO_NAME)
                ->setStreamName($streamName)
                ->setHomepageUrl($this->getHomepageUrl($streamName));
            $this->streamInfos[$streamName] = $streamInfo;
        }
    }

    protected function checkStreamName(string $streamName)
    {
        if (!in_array($streamName, static::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given: ' . $streamName);
        }
    }

    protected function getStreamInfo(string $streamName): StreamInfo
    {
        $this->checkStreamName($streamName);

        if (!array_key_exists($streamName, $this->streamInfos)) {
            throw new \InvalidArgumentException('no radio info object created for stream: ' . $streamName);
        }

        return $this->streamInfos[$streamName];
    }

    abstract protected function getHomepageUrl(string $streamName): string;
}
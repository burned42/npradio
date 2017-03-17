<?php

namespace NPRadio\Stream;

use NPRadio\DataFetcher\DomFetcher;

abstract class RadioStream implements RadioStreamInterface
{
    protected $radioInfos;
    protected $domFetcher;

    const RADIO_NAME = null;
    const AVAILABLE_STREAMS = [];

    public function __construct(DomFetcher $domFetcher)
    {
        $this->domFetcher = $domFetcher;

        foreach (static::AVAILABLE_STREAMS as $streamName) {
            $radioInfo = new RadioInfo();
            $radioInfo->setRadioName(static::RADIO_NAME)
                ->setStreamName($streamName)
                ->setHomepageUrl($this->getHomepageUrl($streamName));
            $this->radioInfos[$streamName] = $radioInfo;
        }
    }

    protected function checkStreamName(string $streamName)
    {
        if (!in_array($streamName, static::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given: ' . $streamName);
        }
    }

    protected function getRadioInfo(string $streamName): RadioInfo
    {
        $this->checkStreamName($streamName);

        if (!array_key_exists($streamName, $this->radioInfos)) {
            throw new \InvalidArgumentException('no radio info object created for stream: ' . $streamName);
        }

        return $this->radioInfos[$streamName];
    }

    abstract protected function getHomepageUrl(string $streamName): string;
}
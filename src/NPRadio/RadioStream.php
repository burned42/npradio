<?php

namespace NPRadio;

abstract class RadioStream implements RadioStreamInterface
{
    /** @var RadioInfo */
    protected $radioInfo;
    protected $streamName;
    protected $domFetcher;

    const RADIO_NAME = null;
    const AVAILABLE_STREAMS = [];

    public function __construct(DomFetcher $domFetcher, $streamName = null)
    {
        $this->domFetcher = $domFetcher;

        $this->radioInfo = new RadioInfo();
        $this->radioInfo->setRadioName(static::RADIO_NAME);

        if (!empty(static::AVAILABLE_STREAMS)) {
            if (!in_array($streamName, static::AVAILABLE_STREAMS)) {
                throw new \InvalidArgumentException('invalid stream name given');
            }

            $this->streamName = $streamName;
            $this->radioInfo->setStreamName($this->streamName);
        }

        $this->radioInfo->setHomepageUrl($this->getHomepageUrl());
    }

    abstract protected function getHomepageUrl(): string;
}
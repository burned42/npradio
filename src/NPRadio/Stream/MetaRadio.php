<?php

namespace NPRadio\Stream;

use NPRadio\DataFetcher\DomFetcher;

class MetaRadio
{
    const RADIOS = [
        MetalOnly::RADIO_NAME => MetalOnly::class,
        RauteMusik::RADIO_NAME => RauteMusik::class,
        TechnoBase::RADIO_NAME => TechnoBase::class
    ];

    /** @var RadioStream[] */
    protected $radios = [];

    protected $streams = [];

    function __construct(DomFetcher $domFetcher)
    {
        /**
         * @var string $radioName
         * @var RadioStream $className
         */
        foreach (self::RADIOS as $radioName => $className) {
            $this->radios[$radioName] = new $className($domFetcher);

            $this->streams[$radioName] = $className::AVAILABLE_STREAMS;
        }
    }

    protected function getRadioName($streamName)
    {
        foreach ($this->streams as $radioName => $availableStreams) {
            if (in_array($streamName, $availableStreams)) {
                return $radioName;
            }
        }

        throw new \InvalidArgumentException('invalid stream name given: ' . $streamName);
    }

    public function getInfo($streamName)
    {
        $radioName = $this->getRadioName($streamName);

        if (!array_key_exists($radioName, $this->radios)) {
            throw new \RuntimeException('could not find radio: ' . $radioName);
        }

        return $this->radios[$radioName]->getInfo($streamName);
    }
}
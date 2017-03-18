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

    function __construct(DomFetcher $domFetcher)
    {
        foreach (self::RADIOS as $radioName => $className) {
            $this->radios[$radioName] = new $className($domFetcher);
        }
    }

    public function getInfo($radioName, $streamName)
    {
        if (!array_key_exists($radioName, $this->radios)) {
            throw new \InvalidArgumentException('invalid radio name given: ' . $radioName);
        }

        return $this->radios[$radioName]->getInfo($streamName);
    }
}
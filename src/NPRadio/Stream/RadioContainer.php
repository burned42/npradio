<?php

namespace NPRadio\Stream;

class RadioContainer
{
    /** @var RadioStream[] */
    protected $radios = [];

    public function addRadio(RadioStream $radioStream)
    {
        if (is_null($radioStream::RADIO_NAME)) {
            throw new \InvalidArgumentException('radio stream has no radio name set');
        }

        if ($this->containsRadio($radioStream::RADIO_NAME)) {
            throw new \RuntimeException('radio stream with this radio name already exists');
        }

        $this->radios[$radioStream::RADIO_NAME] = $radioStream;
    }

    public function containsRadio(string $radioName)
    {
         return array_key_exists($radioName, $this->radios);
    }

    public function getInfo($radioName, $streamName)
    {
        if (!$this->containsRadio($radioName)) {
            throw new \InvalidArgumentException('invalid radio name given: ' . $radioName);
        }

        return $this->radios[$radioName]->getInfo($streamName);
    }
}
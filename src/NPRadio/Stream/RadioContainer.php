<?php

namespace NPRadio\Stream;

class RadioContainer
{
    /** @var RadioStream[] */
    protected $radios = [];

    public function addRadio(RadioStream $radioStream)
    {
        if (is_null($radioStream->getRadioName())) {
            throw new \InvalidArgumentException('radio stream has no radio name set');
        }

        if ($this->containsRadio($radioStream->getRadioName())) {
            throw new \RuntimeException('radio stream with this radio name already exists');
        }

        $this->radios[$radioStream->getRadioName()] = $radioStream;
    }

    public function containsRadio(string $radioName): bool
    {
        return array_key_exists($radioName, $this->radios);
    }

    public function getRadioNames()
    {
        return array_keys($this->radios);
    }

    public function getInfo($radioName, $streamName): StreamInfo
    {
        if (!$this->containsRadio($radioName)) {
            throw new \InvalidArgumentException('invalid radio name given: ' . $radioName);
        }

        return $this->radios[$radioName]->getInfo($streamName);
    }
}
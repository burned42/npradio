<?php

namespace NPRadio\Stream;

class RadioContainer
{
    /** @var RadioStream[] */
    protected $radios = [];

    public function addRadio(RadioStream $radioStream)
    {
        if ($this->containsRadio($radioStream->getRadioName())) {
            throw new \RuntimeException('radio stream with this radio name already exists');
        }

        $this->radios[$radioStream->getRadioName()] = $radioStream;
    }

    public function containsRadio(string $radioName): bool
    {
        return array_key_exists($radioName, $this->radios);
    }

    public function getRadioNames(): array
    {
        return array_keys($this->radios);
    }

    public function getStreamNames(string $radioName): array
    {
        if (!$this->containsRadio($radioName)) {
            throw new \InvalidArgumentException('invalid radio name given: ' . $radioName);
        }

        return $this->radios[$radioName]->getStreamNames();
    }

    public function getInfo(string $radioName, string $streamName): StreamInfo
    {
        if (!$this->containsRadio($radioName)) {
            throw new \InvalidArgumentException('invalid radio name given: ' . $radioName);
        }

        return $this->radios[$radioName]->getInfo($streamName);
    }
}
<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class RadioContainer
{
    /** @var RadioStream[] */
    protected $radios = [];

    /**
     * @param RadioStream $radioStream
     *
     * @throws \RuntimeException
     */
    public function addRadio(RadioStream $radioStream)
    {
        if ($this->containsRadio($radioStream->getRadioName())) {
            throw new \RuntimeException('radio stream with this radio name already exists');
        }

        $this->radios[$radioStream->getRadioName()] = $radioStream;
    }

    /**
     * @param string $radioName
     *
     * @return bool
     */
    public function containsRadio(string $radioName): bool
    {
        return array_key_exists($radioName, $this->radios);
    }

    /**
     * @param string $radioName
     * @param string $streamName
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function containsStream(string $radioName, string $streamName): bool
    {
        return in_array($streamName, $this->getRadio($radioName)->getStreamNames());
    }

    /**
     * @return array
     */
    public function getRadioNames(): array
    {
        return array_keys($this->radios);
    }

    /**
     * @param string $radioName
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getStreamNames(string $radioName): array
    {
        return $this->getRadio($radioName)->getStreamNames();
    }

    /**
     * @param string $radioName
     * @param string $streamName
     *
     * @return StreamInfo
     *
     * @throws \InvalidArgumentException
     */
    public function getInfo(string $radioName, string $streamName): StreamInfo
    {
        return $this->getRadio($radioName)->getInfo($streamName);
    }

    /**
     * @param string $radioName
     *
     * @return RadioStream
     *
     * @throws \InvalidArgumentException
     */
    protected function getRadio($radioName): RadioStream
    {
        if (!$this->containsRadio($radioName)) {
            throw new \InvalidArgumentException('invalid radio name given: '.$radioName);
        }

        return $this->radios[$radioName];
    }
}

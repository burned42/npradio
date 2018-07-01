<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class DummyRadioStream extends RadioStream
{
    const RADIO_NAME = 'fake_radio';
    const AVAILABLE_STREAMS = ['fake_stream'];

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getHomepageUrl(string $streamName): string
    {
        return 'fake_url';
    }

    /**
     * @param string $streamName
     *
     * @return StreamInfo
     *
     * @throws \InvalidArgumentException
     */
    public function getInfo(string $streamName): StreamInfo
    {
        return $this->getStreamInfo($streamName);
    }

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getStreamUrl(string $streamName): string
    {
        return 'fake_stream_url';
    }
}

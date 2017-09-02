<?php

namespace NPRadio\Stream;

class DummyRadioStream extends RadioStream
{
    const RADIO_NAME = 'fake_radio';
    const AVAILABLE_STREAMS = ['fake_stream'];

    protected function getHomepageUrl(string $streamName): string
    {
        return 'fake_url';
    }

    public function getInfo(string $streamName): StreamInfo
    {
        return $this->getStreamInfo($streamName);
    }

    protected function getStreamUrl(string $streamName): string
    {
        return 'fake_stream_url';
    }
}
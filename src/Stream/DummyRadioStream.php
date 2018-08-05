<?php

declare(strict_types=1);

namespace App\Stream;

class DummyRadioStream extends AbstractRadioStream
{
    protected function getHomepageUrl(): string
    {
        return 'fake_url';
    }

    protected function getStreamUrl(): string
    {
        return 'fake_stream_url';
    }

    public function updateInfo()
    {
    }

    public static function getAvailableStreams(): array
    {
        return ['fake_stream'];
    }

    public static function getRadioName(): string
    {
        return 'fake_radio';
    }
}

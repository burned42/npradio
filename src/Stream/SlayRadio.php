<?php

declare(strict_types=1);

namespace App\Stream;

use Exception;
use InvalidArgumentException;
use RuntimeException;

final class SlayRadio extends AbstractRadioStream
{
    private const RADIO_NAME = 'SlayRadio';
    private const URL = 'https://www.slayradio.org';
    private const API_PATH = '/api.php?query=nowplaying';

    private const SLAYRADIO = 'SlayRadio';

    private const AVAILABLE_STREAMS = [
        self::SLAYRADIO,
    ];

    protected function getHomepageUrl(): string
    {
        return self::URL;
    }

    protected function getStreamUrl(): string
    {
        return 'http://relay1.slayradio.org:8000';
    }

    public static function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function updateInfo(): void
    {
        try {
            $url = self::URL.self::API_PATH;
            $data = json_decode($this->getDomFetcher()->getUrlContent($url), true);
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!empty($data) && array_key_exists('data', $data)) {
            if (array_key_exists('artist', $data['data'])) {
                $this->setArtist($data['data']['artist']);
            }
            if (array_key_exists('title', $data['data'])) {
                $this->setTrack($data['data']['title']);
            }
        }
    }
}

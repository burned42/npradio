<?php

declare(strict_types=1);

namespace App\Stream;

use Exception;
use InvalidArgumentException;
use RuntimeException;

final class SlayRadio extends AbstractRadioStream
{
    private const RADIO_NAME = 'SLAY Radio';
    private const URL = 'https://www.slayradio.org';
    private const API_PATH = '/api.php?query=nowplaying';
    private const STREAM_URL = 'http://relay3.slayradio.org:8000';

    private const SLAYRADIO = 'SLAY Radio';

    private const AVAILABLE_STREAMS = [
        self::SLAYRADIO,
    ];

    public function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    public function getStreamInfo(string $streamName): StreamInfo
    {
        if (!in_array($streamName, $this->getAvailableStreams(), true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        $streamInfo = new StreamInfo(
            self::RADIO_NAME,
            $streamName,
            self::URL,
            self::STREAM_URL,
        );

        try {
            $url = self::URL.self::API_PATH;
            $data = json_decode($this->getDomFetcher()->getUrlContent($url), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!empty($data) && array_key_exists('data', $data)) {
            if (array_key_exists('artist', $data['data'])) {
                $streamInfo->artist = $data['data']['artist'];
            }
            if (array_key_exists('title', $data['data'])) {
                $streamInfo->track = $data['data']['title'];
            }
        }

        return $streamInfo;
    }
}

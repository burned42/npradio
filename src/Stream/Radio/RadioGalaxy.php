<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

use function Sentry\captureException;

final class RadioGalaxy extends AbstractRadioStream
{
    private const RADIO_NAME = 'Radio Galaxy';

    private const MITTELFRANKEN = 'Radio Galaxy Mittelfranken';

    private const AVAILABLE_STREAMS = [
        self::MITTELFRANKEN,
    ];

    private const HOMEPAGE_URLS = [
        self::MITTELFRANKEN => 'https://mittelfranken.radiogalaxy.de/',
    ];

    private const STREAM_URLS = [
        self::MITTELFRANKEN => 'https://live.galaxy-mittelfranken.de/galaxy-mittelfranken.mp3',
    ];

    private const INFO_URLS_BY_STREAM = [
        self::MITTELFRANKEN => 'https://mittelfranken.radiogalaxy.de/cache/playlists/all-channels.json',
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
            self::HOMEPAGE_URLS[$streamName],
            self::STREAM_URLS[$streamName],
        );

        try {
            $streamInfo = $this->addTrackAndShowInfo($streamName, $streamInfo);
        } catch (Throwable $t) {
            captureException($t);
        }

        return $streamInfo;
    }

    public function addTrackAndShowInfo(string $streamName, StreamInfo $streamInfo): StreamInfo
    {
        try {
            $url = self::INFO_URLS_BY_STREAM[$streamName];
            $data = json_decode($this->getHttpDataFetcher()->getUrlContent($url), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!is_array($data) || !is_array($data[37])) {
            return $streamInfo;
        }

        $data = $data[37];

        if (array_key_exists('playlist', $data) && !empty($data['playlist'])) {
            $current = array_shift($data['playlist']);
            if (
                is_array($current)
                && array_key_exists('interpret', $current)
                && array_key_exists('title', $current)
            ) {
                $streamInfo->artist = $current['interpret'];
                $streamInfo->track = $current['title'];
            }
        }

        if (!empty($data['show']['title'] ?? null)) {
            $streamInfo->show = trim((string) $data['show']['title']);
        }

        if (!empty($data['show']['host'] ?? null)) {
            $streamInfo->moderator = preg_replace(
                '/^mit /',
                '',
                trim((string) $data['show']['host'])
            );
        }

        return $streamInfo;
    }
}

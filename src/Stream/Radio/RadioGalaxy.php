<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Override;
use Throwable;

use function Sentry\captureException;

final class RadioGalaxy extends AbstractRadioStream
{
    private const string RADIO_NAME = 'Radio Galaxy';

    private const string MITTELFRANKEN = 'Radio Galaxy Mittelfranken';
    /** @var string[] */
    private const array AVAILABLE_STREAMS = [
        self::MITTELFRANKEN,
    ];
    /** @var string[] */
    private const array HOMEPAGE_URLS = [
        self::MITTELFRANKEN => 'https://mittelfranken.radiogalaxy.de/',
    ];
    /** @var string[] */
    private const array STREAM_URLS = [
        self::MITTELFRANKEN => 'https://live.galaxy-mittelfranken.de/galaxy-mittelfranken.mp3',
    ];
    /** @var string[] */
    private const array INFO_URLS_BY_STREAM = [
        self::MITTELFRANKEN => 'https://mittelfranken.radiogalaxy.de/cache/playlists/all-channels.json',
    ];

    #[Override]
    public function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    #[Override]
    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    #[Override]
    public function getStreamInfo(string $streamName): StreamInfo
    {
        $this->assertValidStreamName($streamName);

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
        $url = self::INFO_URLS_BY_STREAM[$streamName];
        $data = $this->getHttpDataFetcher()->getJsonData($url);

        if (!is_array($data[37])) {
            return $streamInfo;
        }

        $data = $data[37];

        if (
            array_key_exists('playlist', $data)
            && is_array($data['playlist'])
            && [] !== $data['playlist']
        ) {
            $current = array_shift($data['playlist']);
            if (
                is_array($current)
                && is_string($current['interpret'] ?? null)
                && is_string($current['title'] ?? null)
            ) {
                $streamInfo->artist = $current['interpret'];
                $streamInfo->track = $current['title'];
            }
        }

        if (!is_array($data['show'])) {
            return $streamInfo;
        }

        if (is_string($data['show']['title'] ?? null)) {
            $streamInfo->show = trim($data['show']['title']);
        }

        if (is_string($data['show']['host'] ?? null)) {
            $streamInfo->moderator = preg_replace(
                '/^mit /',
                '',
                trim($data['show']['host'])
            );
        }

        return $streamInfo;
    }
}

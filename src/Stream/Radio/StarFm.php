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

final class StarFm extends AbstractRadioStream
{
    private const RADIO_NAME = 'STAR FM';
    private const HOMEPAGE_URL = 'https://nbg.starfm.de';

    private const NUREMBERG = 'STAR FM Nürnberg';
    private const FROM_HELL = 'STAR FM From Hell';

    private const AVAILABLE_STREAMS = [
        self::FROM_HELL,
        self::NUREMBERG,
    ];

    private const STREAM_URLS = [
        self::FROM_HELL => 'https://starfm-4.explodio.com/hell.mp3',
        self::NUREMBERG => 'https://starfm-1.explodio.com/nuernberg.mp3',
    ];
    private const STREAM_INFO_URL = 'https://nbg.starfm.de/services/program-info/live/starfm';
    private const STREAM_INFO_API_NAMES = [
        self::FROM_HELL => 'fromhell',
        self::NUREMBERG => 'nbg',
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
            self::HOMEPAGE_URL,
            self::STREAM_URLS[$streamName],
        );

        try {
            $streamInfo = $this->addTrackInfo($streamName, $streamInfo);
        } catch (Throwable $t) {
            captureException($t);
        }

        return $streamInfo;
    }

    public function addTrackInfo(string $streamName, StreamInfo $streamInfo): StreamInfo
    {
        try {
            $data = json_decode(
                $this->getHttpDataFetcher()->getUrlContent(self::STREAM_INFO_URL),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        $apiStreamName = self::STREAM_INFO_API_NAMES[$streamName];
        $data = array_filter(
            $data,
            static fn ($streamData) => $apiStreamName === ($streamData['name'] ?? null),
        );

        $trackInfo = $data[array_key_first($data)] ?? null;
        $track = $trackInfo['playHistories'][0]['track']['title'] ?? null;
        if (is_string($track) && !empty($track)) {
            $streamInfo->track = $track;
        }
        $artist = $trackInfo['playHistories'][0]['track']['artist'] ?? null;
        if (is_string($artist) && !empty($artist)) {
            $streamInfo->artist = $artist;
        }

        return $streamInfo;
    }
}

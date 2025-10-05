<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Exception;
use Override;
use RuntimeException;
use Throwable;

use function Sentry\captureException;

final class StarFm extends AbstractRadioStream
{
    private const string RADIO_NAME = 'STAR FM';
    private const string HOMEPAGE_URL = 'https://nbg.starfm.de';

    private const string NUREMBERG = 'STAR FM Nürnberg';
    private const string FROM_HELL = 'STAR FM From Hell';
    /** @var string[] */
    private const array AVAILABLE_STREAMS = [
        self::FROM_HELL,
        self::NUREMBERG,
    ];
    /** @var string[] */
    private const array STREAM_URLS = [
        self::FROM_HELL => 'https://stream.starfm.de/fromhell/mp3-192/webseite',
        self::NUREMBERG => 'https://stream.starfm.de/nbg/mp3-192/webseite',
    ];
    private const string STREAM_INFO_BASE_URL = 'https://api.streamabc.net/metadata/channel/';
    /** @var string[] */
    private const array STREAM_INFO_URL_PATHS = [
        self::FROM_HELL => '30_lpuzm574hotr_d953.json',
        self::NUREMBERG => '30_nbw9xzg7b53v_rgfj.json',
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

    private function addTrackInfo(string $streamName, StreamInfo $streamInfo): StreamInfo
    {
        try {
            $url = self::STREAM_INFO_BASE_URL.self::STREAM_INFO_URL_PATHS[$streamName];
            $data = json_decode(
                $this->getHttpDataFetcher()->getUrlContent($url),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!is_array($data)) {
            return $streamInfo;
        }

        $track = $data['song'] ?? null;
        if (is_string($track) && '' !== $track) {
            $streamInfo->track = $track;
        }
        $artist = $data['artist'];
        if (is_string($artist) && '' !== $artist) {
            $streamInfo->artist = $artist;
        }

        return $streamInfo;
    }
}

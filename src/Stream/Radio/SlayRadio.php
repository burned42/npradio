<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Exception;
use InvalidArgumentException;
use Override;
use RuntimeException;
use Throwable;

use function Sentry\captureException;

final class SlayRadio extends AbstractRadioStream
{
    private const string RADIO_NAME = 'SLAY Radio';
    private const string URL = 'https://www.slayradio.org';
    private const string API_PATH = '/api.php?query=nowplaying';
    private const string STREAM_URL = 'http://relay3.slayradio.org:8000';

    private const string SLAYRADIO = 'SLAY Radio';
    /** @var string[] */
    private const array AVAILABLE_STREAMS = [
        self::SLAYRADIO,
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
            $streamInfo = $this->addTrackInfo($streamInfo);
        } catch (Throwable $t) {
            captureException($t);
        }

        return $streamInfo;
    }

    public function addTrackInfo(StreamInfo $streamInfo): StreamInfo
    {
        try {
            $url = self::URL.self::API_PATH;
            $data = json_decode($this->getHttpDataFetcher()->getUrlContent($url), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (
            is_array($data)
            && is_array($data['data'] ?? null)
            && is_string($data['data']['artist'] ?? null)
            && is_string($data['data']['title'] ?? null)
        ) {
            $streamInfo->artist = $data['data']['artist'];
            $streamInfo->track = $data['data']['title'];
        }

        return $streamInfo;
    }
}

<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Override;
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
        $this->assertValidStreamName($streamName);

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
        $url = self::URL.self::API_PATH;
        $data = $this->getHttpDataFetcher()->getJsonData($url);

        if (
            is_array($data['data'] ?? null)
            && is_string($data['data']['artist'] ?? null)
            && is_string($data['data']['title'] ?? null)
        ) {
            $streamInfo->artist = $data['data']['artist'];
            $streamInfo->track = $data['data']['title'];
        }

        return $streamInfo;
    }
}

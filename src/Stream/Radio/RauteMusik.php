<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use DateTimeImmutable;
use Exception;
use Override;
use RuntimeException;
use Throwable;

use function Sentry\captureException;

final class RauteMusik extends AbstractRadioStream
{
    private const string RADIO_NAME = 'RauteMusik';
    private const string BASE_URL = 'https://www.rm.fm/';
    private const string API_URL = 'https://api.rautemusik.fm';

    private const string MAIN = 'RauteMusik Main';
    private const string CLUB = 'RauteMusik Club';
    private const string CHRISTMAS = 'RauteMusik Christmas';
    private const string HAPPYHARDCORE = 'RauteMusik HappyHardcore';
    private const string HARDER = 'RauteMusik HardeR';
    private const string HOUSE = 'RauteMusik House';
    private const string METAL = 'RauteMusik Metal';
    private const string ROCK = 'RauteMusik Rock';
    private const string TECHHOUSE = 'RauteMusik TechHouse';
    private const string WEIHNACHTEN = 'RauteMusik Weihnachten';
    /** @var string[] */
    private const array AVAILABLE_STREAMS = [
        self::MAIN,
        self::CLUB,
        self::CHRISTMAS,
        self::HAPPYHARDCORE,
        self::HARDER,
        self::HOUSE,
        self::METAL,
        self::ROCK,
        self::TECHHOUSE,
        self::WEIHNACHTEN,
    ];

    private const int CACHE_DURATION = 120;

    private function getStreamNameForUrl(string $streamName): string
    {
        return strtolower(
            str_replace(
                ['RauteMusik', ' '],
                '',
                $streamName
            )
        );
    }

    private function getHomepageUrl(string $streamName): string
    {
        return self::BASE_URL.$this->getStreamNameForUrl($streamName);
    }

    private function getStreamUrl(string $streamName): string
    {
        return 'https://'.$this->getStreamNameForUrl($streamName).'-high.rautemusik.fm';
    }

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

    /**
     * @throws Exception
     */
    #[Override]
    public function getStreamInfo(string $streamName): StreamInfo
    {
        $this->assertValidStreamName($streamName);

        $streamInfo = new StreamInfo(
            self::RADIO_NAME,
            $streamName,
            $this->getHomepageUrl($streamName),
            $this->getStreamUrl($streamName),
        );

        try {
            $streamInfo = $this->addTrackInfo($streamInfo);
            $streamInfo = $this->addShowInfo($streamInfo);
        } catch (Throwable $t) {
            captureException($t);
        }

        return $streamInfo;
    }

    /**
     * @return array<mixed>
     */
    private function getApiData(string $path): array
    {
        $timestamp = (string) time();
        $hash = sha1($timestamp.'17426'.$path.'Wo+AEi47[ajKJpPgb1EU0QLLS355R{cz');
        $hashPart = substr($hash, 0, 12);

        return $this->getHttpDataFetcher()->getJsonData(
            self::API_URL.$path,
            [
                'Accept' => 'application/json',
                'x-client-id' => '17426',
                'x-timestamp' => $timestamp,
                'x-hash' => $hashPart,
            ],
            self::CACHE_DURATION
        );
    }

    /**
     * @throws RuntimeException
     */
    private function addTrackInfo(StreamInfo $streamInfo): StreamInfo
    {
        $streamName = $this->getStreamNameForUrl($streamInfo->streamName);
        $data = $this->getApiData('/streams/'.$streamName.'/tracks/');

        if (!is_array($data['items'] ?? null)) {
            return $streamInfo;
        }

        $currentTrack = $data['items'][0] ?? null;
        if (
            is_array($currentTrack)
            && is_array($currentTrack['track'] ?? null)
            && is_array($currentTrack['artist'] ?? null)
            && is_string($currentTrack['track']['name'] ?? null)
            && is_string($currentTrack['artist']['name'] ?? null)
        ) {
            $streamInfo->track = $currentTrack['track']['name'];
            $streamInfo->artist = $currentTrack['artist']['name'];
        }

        return $streamInfo;
    }

    /**
     * @throws Exception
     */
    private function addShowInfo(StreamInfo $streamInfo): StreamInfo
    {
        $streamName = $this->getStreamNameForUrl($streamInfo->streamName);
        $data = $this->getApiData('/streams_onair/');

        if (!is_array($data['items'] ?? null)) {
            return $streamInfo;
        }

        $streamData = array_filter(
            $data['items'],
            static fn ($stream): bool => is_array($stream) && $streamName === ($stream['id'] ?? null)
        );
        if (1 !== count($streamData)) {
            return $streamInfo;
        }

        $currentStreamData = array_shift($streamData);
        if (!is_array($currentStreamData)) {
            return $streamInfo;
        }

        $currentShow = $currentStreamData['show'] ?? null;
        if (!is_array($currentShow)) {
            return $streamInfo;
        }

        $startTime = $currentShow['start_time'] ?? null;
        $endTime = $currentShow['end_time'] ?? null;
        if (is_string($startTime) && is_string($endTime)) {
            $streamInfo->showStartTime = new DateTimeImmutable($startTime);
            $streamInfo->showEndTime = new DateTimeImmutable($endTime);
        }

        if (is_string($currentShow['name'] ?? null)) {
            $streamInfo->show = $currentShow['name'];
        }

        if (
            !is_array($currentShow['moderator'])
            || !is_string($currentShow['moderator']['username'] ?? null)
        ) {
            return $streamInfo;
        }

        $moderator = $currentShow['moderator']['username'];

        $coModerators = $currentShow['co_moderators'] ?? null;
        if (is_array($coModerators)) {
            $coModeratorNames = array_filter(array_map(
                static fn ($data): ?string => (is_array($data) && is_string($data['username'] ?? null))
                    ? $data['username']
                    : null,
                $coModerators
            ));
            if (count($coModeratorNames) > 0) {
                $moderator .= ', '.implode(', ', $coModeratorNames);
            }
        }
        $streamInfo->moderator = $moderator;

        return $streamInfo;
    }
}

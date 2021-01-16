<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class RauteMusik extends AbstractRadioStream
{
    private const RADIO_NAME = 'RauteMusik';
    private const BASE_URL = 'https://www.rm.fm/';
    private const API_URL = 'https://api.rautemusik.fm';

    private const MAIN = 'RauteMusik Main';
    private const CLUB = 'RauteMusik Club';
    private const CHRISTMAS = 'RauteMusik Christmas';
    private const HAPPYHARDCORE = 'RauteMusik HappyHardcore';
    private const HARDER = 'RauteMusik HardeR';
    private const HOUSE = 'RauteMusik House';
    private const ROCK = 'RauteMusik Rock';
    private const TECHHOUSE = 'RauteMusik TechHouse';
    private const WACKENRADIO = 'Wacken Radio';
    private const WEIHNACHTEN = 'RauteMusik Weihnachten';

    private const AVAILABLE_STREAMS = [
        self::MAIN,
        self::CLUB,
        self::CHRISTMAS,
        self::HAPPYHARDCORE,
        self::HARDER,
        self::HOUSE,
        self::ROCK,
        self::TECHHOUSE,
        self::WACKENRADIO,
        self::WEIHNACHTEN,
    ];

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

    protected function getHomepageUrl(string $streamName): string
    {
        return self::BASE_URL.$this->getStreamNameForUrl($streamName);
    }

    protected function getStreamUrl(string $streamName): string
    {
        return 'https://'.$this->getStreamNameForUrl($streamName).'-high.rautemusik.fm';
    }

    public function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    /**
     * @throws Exception
     */
    public function getStreamInfo(string $streamName): StreamInfo
    {
        if (!in_array($streamName, $this->getAvailableStreams(), true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        $streamInfo = new StreamInfo(
            self::RADIO_NAME,
            $streamName,
            $this->getHomepageUrl($streamName),
            $this->getStreamUrl($streamName),
        );

        $streamInfo = $this->addTrackInfo($streamInfo);
        $streamInfo = $this->addShowInfo($streamInfo);

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

        return $this->getDomFetcher()->getJsonData(
            self::API_URL.$path,
            [
                'Accept' => 'application/json',
                'x-client-id' => '17426',
                'x-timestamp' => $timestamp,
                'x-hash' => $hashPart,
            ]
        );
    }

    /**
     * @throws RuntimeException
     */
    private function addTrackInfo(StreamInfo $streamInfo): StreamInfo
    {
        try {
            $streamName = $this->getStreamNameForUrl($streamInfo->streamName);
            $data = $this->getApiData('/streams/'.$streamName.'/tracks/');
        } catch (Throwable $t) {
            throw new RuntimeException('could not fetch track info: '.$t->getMessage());
        }

        $currentTrack = $data['items'][0] ?? null;
        $streamInfo->track = $currentTrack['track']['name'] ?? null;
        $streamInfo->artist = $currentTrack['artist']['name'] ?? null;

        return $streamInfo;
    }

    /**
     * @throws Exception
     */
    private function addShowInfo(StreamInfo $streamInfo): StreamInfo
    {
        try {
            $streamName = $this->getStreamNameForUrl($streamInfo->streamName);
            $data = $this->getApiData('/streams_onair/');
        } catch (Throwable $t) {
            throw new RuntimeException('could not fetch show info: '.$t->getMessage());
        }

        $streamData = array_filter(
            $data['items'],
            static fn ($stream) => $streamName === $stream['id']
        );
        if (empty($streamData) || 1 !== count($streamData)) {
            return $streamInfo;
        }

        $currentShow = $streamData[array_key_first($streamData)]['show'] ?? null;

        $startTime = $currentShow['start_time'] ?? null;
        $endTime = $currentShow['end_time'] ?? null;
        if (is_string($startTime) && is_string($endTime)) {
            $streamInfo->showStartTime = new DateTimeImmutable($startTime);
            $streamInfo->showEndTime = new DateTimeImmutable($endTime);
        }

        $streamInfo->show = $currentShow['name'] ?? null;

        $moderator = $currentShow['moderator']['username'];
        $coModerators = $currentShow['co_moderators'] ?? null;
        if (is_array($coModerators) && !empty($coModerators)) {
            $coModeratorNames = array_filter(array_map(
                static fn ($data) => $data['username'] ?? null,
                $coModerators
            ));
            if (!empty($coModeratorNames)) {
                $moderator .= ', '.implode(', ', $coModeratorNames);
            }
        }
        $streamInfo->moderator = $moderator;

        return $streamInfo;
    }
}

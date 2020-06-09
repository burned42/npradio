<?php

declare(strict_types=1);

namespace App\Stream;

use Exception;
use InvalidArgumentException;
use RuntimeException;

final class StarFm extends AbstractRadioStream
{
    private const RADIO_NAME = 'STAR FM';
    private const URL = 'https://nbg.starfm.de';

    private const NUREMBERG = 'STAR FM Nürnberg';
    private const FROM_HELL = 'STAR FM From Hell';

    private const AVAILABLE_STREAMS = [
        self::FROM_HELL,
        self::NUREMBERG,
    ];

    private const STREAM_URLS = [
        self::FROM_HELL => 'http://starfm-4.explodio.com/hell.mp3',
        self::NUREMBERG => 'http://starfm-1.explodio.com/nuernberg.mp3',
    ];

    private const URL_INFO_BASE_PATH = '/player/cache/currentSong/currentSong_';
    private const URL_INFO_SUFFIX = '.json';
    private const URL_INFO_STREAM_NAMES = [
        self::FROM_HELL => '2',
        self::NUREMBERG => '4',
    ];

    protected function getHomepageUrl(): string
    {
        return self::URL;
    }

    public function getStreamUrl(): string
    {
        return self::STREAM_URLS[$this->getStreamName()];
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
            $streamName = self::URL_INFO_STREAM_NAMES[$this->getStreamName()];
            $url = self::URL.self::URL_INFO_BASE_PATH.$streamName.self::URL_INFO_SUFFIX;
            $data = json_decode($this->getDomFetcher()->getUrlContent($url), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!empty($data) && array_key_exists('c', $data)) {
            if (array_key_exists('artist', $data['c'])) {
                $this->setArtist($data['c']['artist']);
            }
            if (array_key_exists('song', $data['c'])) {
                $this->setTrack($data['c']['song']);
            }
        }
    }
}

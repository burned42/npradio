<?php

declare(strict_types=1);

namespace App\Stream;

final class StarFm extends AbstractRadioStream
{
    const RADIO_NAME = 'STAR FM';
    const URL = 'https://nbg.starfm.de';

    const NUREMBERG = 'Nürnberg';
    const FROM_HELL = 'From Hell';

    const AVAILABLE_STREAMS = [
        self::FROM_HELL,
        self::NUREMBERG,
    ];

    const STREAM_URLS = [
        self::FROM_HELL => 'http://85.25.43.55:80/hell.mp3',
        self::NUREMBERG => 'http://85.25.209.150:80/nuernberg.mp3',
    ];

    const URL_INFO_BASE_PATH = '/player/cache/currentSong/currentSong_';
    const URL_INFO_SUFFIX = '.json';
    const URL_INFO_STREAM_NAMES = [
        self::FROM_HELL => '2',
        self::NUREMBERG => '4',
    ];

    protected function getHomepageUrl(): string
    {
        return self::URL;
    }

    protected function getStreamUrl(): string
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function updateInfo()
    {
        try {
            $url = self::URL.self::URL_INFO_BASE_PATH.self::URL_INFO_STREAM_NAMES[$this->getStreamName()].self::URL_INFO_SUFFIX;
            $data = json_decode($this->domFetcher->getUrlContent($url), true);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get url content: '.$e->getMessage());
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

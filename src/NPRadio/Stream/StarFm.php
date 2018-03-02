<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class StarFm extends RadioStream
{
    const RADIO_NAME = 'StarFM';
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

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getHomepageUrl(string $streamName): string
    {
        return self::URL;
    }

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getStreamUrl(string $streamName): string
    {
        return self::STREAM_URLS[$streamName];
    }

    /**
     * @param string $streamName
     *
     * @return StreamInfo
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getInfo(string $streamName): StreamInfo
    {
        $streamInfo = $this->getStreamInfo($streamName);

        try {
            $url = self::URL.self::URL_INFO_BASE_PATH.self::URL_INFO_STREAM_NAMES[$streamName].self::URL_INFO_SUFFIX;
            $data = json_decode($this->domFetcher->getUrlContent($url), true);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!empty($data) && array_key_exists('c', $data)) {
            if (array_key_exists('artist', $data['c'])) {
                $streamInfo->setArtist($data['c']['artist']);
            }
            if (array_key_exists('song', $data['c'])) {
                $streamInfo->setTrack($data['c']['song']);
            }
        }

        return $streamInfo;
    }
}

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

    const URL_INFO_BASE_PATH = '/hoeren/playlist/playlist-';
    const URL_INFO_STREAM_NAMES = [
        self::FROM_HELL => 'from-hell',
        self::NUREMBERG => 'nuernberg',
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
            $infoUrl = self::URL.self::URL_INFO_BASE_PATH.self::URL_INFO_STREAM_NAMES[$streamName];
            $dom = $this->domFetcher->getHtmlDom($infoUrl);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new \DOMXPath($dom);

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='playlist']//table[@class='playlist']//tr[@class='active']//td[2]");
        if (1 === $nodeList->length) {
            $matches = [];
            if (preg_match('/^(.*) - ([^-]*)$/', $nodeList->item(0)->nodeValue, $matches)) {
                $streamInfo->setArtist(trim($matches[1]));
                $streamInfo->setTrack(trim($matches[2]));
            }
        }

        return $streamInfo;
    }
}

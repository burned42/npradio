<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class RauteMusik extends RadioStream
{
    const RADIO_NAME = 'RauteMusik';

    const BASE_URL = 'http://www.rautemusik.fm/';
    const SHOW_INFO_URL = self::BASE_URL.'radio/sendeplan/';

    const MAIN = 'Main';
    const CLUB = 'Club';
    const CHRISTMAS = 'Christmas';
    const HAPPYHARDCORE = 'HappyHardcore';
    const HOUSE = 'House';
    const ROCK = 'Rock';
    const TECHHOUSE = 'TechHouse';
    const WACKENRADIO = 'WackenRadio';
    const WEIHNACHTEN = 'Weihnachten';

    const AVAILABLE_STREAMS = [
        self::MAIN,
        self::CLUB,
        self::CHRISTMAS,
        self::HAPPYHARDCORE,
        self::HOUSE,
        self::ROCK,
        self::TECHHOUSE,
        self::WACKENRADIO,
        self::WEIHNACHTEN,
    ];

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getHomepageUrl(string $streamName): string
    {
        return self::BASE_URL.strtolower($streamName);
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

        $this->fetchTrackInfo($streamInfo, $streamName);
        $this->fetchShowInfo($streamInfo, $streamName);

        return $streamInfo;
    }

    /**
     * @param StreamInfo $streamInfo
     * @param string     $streamName
     *
     * @throws \RuntimeException
     */
    private function fetchTrackInfo(StreamInfo $streamInfo, string $streamName)
    {
        try {
            $dom = $this->domFetcher->getHtmlDom(self::BASE_URL.strtolower($streamName));
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new \DOMXPath($dom);
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//li[@class='current']//p[@class='title'] | .//li[@class='current']//p[@class='artist']");

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            $class = $node->attributes->getNamedItem('class')->nodeValue;
            if ('artist' === $class) {
                $streamInfo->setArtist(trim($node->nodeValue));
            } elseif ('title' === $class) {
                $streamInfo->setTrack(trim($node->nodeValue));
            }
        }
    }

    /**
     * @param StreamInfo $streamInfo
     * @param string     $streamName
     *
     * @throws \RuntimeException
     */
    private function fetchShowInfo(StreamInfo $streamInfo, string $streamName)
    {
        try {
            $dom = $this->domFetcher->getHtmlDom(self::SHOW_INFO_URL.strtolower($streamName));
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new \DOMXPath($dom);
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//tr[@class='current']//td");

        $numNodes = $nodeList->length;
        if ($numNodes >= 1) {
            $matches = [];
            if (preg_match('/^(\d{2}:\d{2}) - (\d{2}:\d{2}) Uhr$/', $nodeList->item(0)->nodeValue, $matches)) {
                $streamInfo->setShowStartTime(new \DateTime($matches[1]));
                $streamInfo->setShowEndTime(new \DateTime($matches[2]));
            }

            if ($numNodes >= 2) {
                $streamInfo->setShow(trim($nodeList->item(1)->nodeValue));

                if ($numNodes >= 3) {
                    $streamInfo->setModerator(
                        preg_replace('/\s+/', ' ', trim($nodeList->item(2)->nodeValue))
                    );
                }
            }
        }
    }

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getStreamUrl(string $streamName): string
    {
        return 'http://'.strtolower($streamName).'-high.rautemusik.fm';
    }
}
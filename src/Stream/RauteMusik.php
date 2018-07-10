<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class RauteMusik extends AbstractRadioStream
{
    const RADIO_NAME = 'RauteMusik';
    const BASE_URL = 'https://www.rautemusik.fm/';
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

    protected function getHomepageUrl(): string
    {
        return self::BASE_URL.strtolower($this->getStreamName());
    }

    protected function getStreamUrl(): string
    {
        return 'http://'.strtolower($this->getStreamName()).'-high.rautemusik.fm';
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
        $this->updateTrackInfo();
        $this->updateShowInfo();
    }

    /**
     * @throws \RuntimeException
     */
    private function updateTrackInfo()
    {
        try {
            $dom = $this->domFetcher->getHtmlDom(self::BASE_URL.strtolower($this->getStreamName()));
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
                $this->setArtist(trim($node->nodeValue));
            } elseif ('title' === $class) {
                $this->setTrack(trim($node->nodeValue));
            }
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function updateShowInfo()
    {
        try {
            $dom = $this->domFetcher->getHtmlDom(self::SHOW_INFO_URL.strtolower($this->getStreamName()));
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
                $this->setShowStartTime(new \DateTime($matches[1]));
                $this->setShowEndTime(new \DateTime($matches[2]));
            }

            if ($numNodes >= 2) {
                $this->setShow(trim($nodeList->item(1)->nodeValue));

                if ($numNodes >= 3) {
                    $this->setModerator(
                        preg_replace('/\s+/', ' ', trim($nodeList->item(2)->nodeValue))
                    );
                }
            }
        }
    }
}

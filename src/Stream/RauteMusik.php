<?php

declare(strict_types=1);

namespace App\Stream;

final class RauteMusik extends AbstractRadioStream
{
    private const RADIO_NAME = 'RauteMusik';
    private const BASE_URL = 'https://www.rautemusik.fm/';
    private  const SHOW_INFO_URL = self::BASE_URL.'radio/sendeplan/';

    private const MAIN = 'Main';
    private const CLUB = 'Club';
    private const CHRISTMAS = 'Christmas';
    private const HAPPYHARDCORE = 'HappyHardcore';
    private const HOUSE = 'House';
    private const ROCK = 'Rock';
    private const TECHHOUSE = 'TechHouse';
    private const WACKENRADIO = 'WackenRadio';
    private const WEIHNACHTEN = 'Weihnachten';

    private const AVAILABLE_STREAMS = [
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
     * @throws \Exception
     */
    public function updateInfo(): void
    {
        $this->updateTrackInfo();
        $this->updateShowInfo();
    }

    /**
     * @throws \RuntimeException
     */
    private function updateTrackInfo(): void
    {
        try {
            $dom = $this->getDomFetcher()->getHtmlDom(self::BASE_URL.strtolower($this->getStreamName()));
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new \DOMXPath($dom);
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(
            ".//li[@class='current']//p[@class='title']"
            ." | .//li[@class='current']//p[@class='artist']"
        );

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
     * @throws \Exception
     */
    private function updateShowInfo(): void
    {
        try {
            $dom = $this->getDomFetcher()->getHtmlDom(self::SHOW_INFO_URL.strtolower($this->getStreamName()));
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

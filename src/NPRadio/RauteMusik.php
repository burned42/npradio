<?php

namespace NPRadio;

class RauteMusik extends RadioStream
{
    const RADIO_NAME = '#Musik';

    const BASE_URL = 'http://www.rautemusik.fm/';
    const SHOW_INFO_URL = self::BASE_URL . 'radio/sendeplan/';

    const MAIN = 'Main';
    const CLUB = 'Club';
    const HOUSE = 'House';
    const WACKENRADIO = 'WackenRadio';
    const METAL = 'Metal;';

    const AVAILABLE_STREAMS = [
        self::MAIN,
        self::CLUB,
        self::HOUSE,
        self::WACKENRADIO,
        self::METAL
    ];

    protected function getHomepageUrl(): string
    {
        return self::BASE_URL . strtolower($this->streamName);
    }

    public function getInfo(): RadioInfo
    {
        $this->fetchTrackInfo();
        $this->fetchShowInfo();

        return $this->radioInfo;
    }

    private function fetchTrackInfo()
    {
        try {
            $dom = $this->domFetcher->getHtmlDom(self::BASE_URL . strtolower($this->streamName));
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: ' . $e->getMessage());
        }

        $xpath = new \DOMXPath($dom);
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//li[@class='current']//p[@class='title'] | .//li[@class='current']//p[@class='artist']");

        /** @var \DOMNode $node */
        foreach ($nodeList as $node) {
            $class = $node->attributes->getNamedItem('class')->nodeValue;
            if ($class === 'artist') {
                $this->radioInfo->setArtist($node->nodeValue);
            } elseif ($class === 'title') {
                $this->radioInfo->setTrack($node->nodeValue);
            }
        }
    }

    private function fetchShowInfo()
    {
        try {
            $dom = $this->domFetcher->getHtmlDom(self::SHOW_INFO_URL . strtolower($this->streamName));
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: ' . $e->getMessage());
        }

        $xpath = new \DOMXPath($dom);
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//tr[@class='current']//td");

        $numNodes = $nodeList->length;
        if ($numNodes >= 1) {
            $matches = [];
            if (preg_match('/^([0-9]{2}:[0-9]{2}) - ([0-9]{2}:[0-9]{2}) Uhr$/', $nodeList->item(0)->nodeValue, $matches)) {
                $this->radioInfo->setShowStartTime(new \DateTime($matches[1]));
                $this->radioInfo->setShowEndTime(new \DateTime($matches[2]));
            }

            if ($numNodes >= 2) {
                $this->radioInfo->setShow(trim($nodeList->item(1)->nodeValue));

                if ($numNodes >= 3) {
                    $this->radioInfo->setModerator(trim($nodeList->item(2)->nodeValue));
                }
            }
        }
    }
}
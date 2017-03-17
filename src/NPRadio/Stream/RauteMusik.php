<?php

namespace NPRadio\Stream;

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

    protected function getHomepageUrl(string $streamName): string
    {
        $this->checkStreamName($streamName);

        return self::BASE_URL . strtolower($streamName);
    }

    public function getInfo(string $streamName): RadioInfo
    {
        $radioInfo = $this->getRadioInfo($streamName);

        $this->fetchTrackInfo($radioInfo, $streamName);
        $this->fetchShowInfo($radioInfo, $streamName);

        return $radioInfo;
    }

    private function fetchTrackInfo(RadioInfo $radioInfo, string $streamName)
    {
        $this->checkStreamName($streamName);

        try {
            $dom = $this->domFetcher->getHtmlDom(self::BASE_URL . strtolower($streamName));
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
                $radioInfo->setArtist($node->nodeValue);
            } elseif ($class === 'title') {
                $radioInfo->setTrack($node->nodeValue);
            }
        }
    }

    private function fetchShowInfo(RadioInfo $radioInfo, string $streamName)
    {
        $this->checkStreamName($streamName);

        try {
            $dom = $this->domFetcher->getHtmlDom(self::SHOW_INFO_URL . strtolower($streamName));
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
                $radioInfo->setShowStartTime(new \DateTime($matches[1]));
                $radioInfo->setShowEndTime(new \DateTime($matches[2]));
            }

            if ($numNodes >= 2) {
                $radioInfo->setShow(trim($nodeList->item(1)->nodeValue));

                if ($numNodes >= 3) {
                    $radioInfo->setModerator(trim($nodeList->item(2)->nodeValue));
                }
            }
        }
    }
}
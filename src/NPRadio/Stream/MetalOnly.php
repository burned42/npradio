<?php

namespace NPRadio\Stream;

class MetalOnly extends RadioStream
{
    const RADIO_NAME = 'MetalOnly';
    const URL = 'https://www.metal-only.de';
    # use page 'Impressum' because there is only text and the page should load quicker
    const URL_INFO_PATH = '/impressum.html';

    const METAL_ONLY = 'MetalOnly';

    const AVAILABLE_STREAMS = [
        self::METAL_ONLY
    ];

    protected function getHomepageUrl(string $streamName): string
    {
        return self::URL;
    }

    public function getInfo(string $streamName): StreamInfo
    {
        $streamInfo = $this->getStreamInfo($streamName);

        try {
            $dom = $this->domFetcher->getHtmlDom(self::URL);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: ' . $e->getMessage());
        }

        $xpath = new \DOMXPath($dom);

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='headline']");
        if ($nodeList->length === 1) {
            $matches = [];
            if (preg_match('/^(.*) ist ON AIR$/', $nodeList->item(0)->nodeValue, $matches)) {
                $streamInfo->setModerator(trim($matches[1]));
            }
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='sendung']//span");
        if ($nodeList->length === 1) {
            $streamInfo->setShow(trim($nodeList->item(0)->nodeValue));
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='gerne']//span");
        if ($nodeList->length === 1) {
            $streamInfo->setGenre(trim($nodeList->item(0)->nodeValue));
        }

        // if there is no show/moderator then it displays some default values
        if (
            $streamInfo->getModerator() === 'MetalHead'
            && $streamInfo->getShow() === 'Keine Grüsse und Wünsche möglich.'
            && $streamInfo->getGenre() === 'Mixed Metal'
        ) {
            $streamInfo->setModerator(null)
                ->setShow(null)
                ->setGenre(null);
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='track']//span");
        if ($nodeList->length === 1) {
            $matches = [];
            if (preg_match('/^(.*) - ([^-]*)$/', $nodeList->item(0)->nodeValue, $matches)) {
                $streamInfo->setArtist(trim($matches[1]));
                $streamInfo->setTrack(trim($matches[2]));
            }
        }

        return $streamInfo;
    }
}

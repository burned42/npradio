<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class MetalOnly extends RadioStream
{
    const RADIO_NAME = 'MetalOnly';
    const URL = 'https://www.metal-only.de';
    // use page 'Impressum' because there is only text and the page should load quicker
    const URL_INFO_PATH = '/sendeplan.html';
    const STREAM_URL = 'http://server1.blitz-stream.de:4400/;';

    const METAL_ONLY = 'MetalOnly';

    const AVAILABLE_STREAMS = [
        self::METAL_ONLY,
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
     * @return StreamInfo
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getInfo(string $streamName): StreamInfo
    {
        $streamInfo = $this->getStreamInfo($streamName);

        try {
            $dom = $this->domFetcher->getHtmlDom(self::URL.self::URL_INFO_PATH);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new \DOMXPath($dom);

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='headline']");
        if (1 === $nodeList->length) {
            $matches = [];
            if (preg_match('/^(.*) ist ON AIR$/', $nodeList->item(0)->nodeValue, $matches)) {
                $streamInfo->setModerator(trim($matches[1]));
            }
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='sendung']//span");
        if (1 === $nodeList->length) {
            $streamInfo->setShow(trim($nodeList->item(0)->nodeValue));
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='gerne']//span");
        if (1 === $nodeList->length) {
            $streamInfo->setGenre(trim($nodeList->item(0)->nodeValue));
        }

        // if there is no show/moderator then it displays some default values
        if (
            'MetalHead' === $streamInfo->getModerator()
            && 'Mixed Metal' === $streamInfo->getGenre()
            && \in_array($streamInfo->getShow(), ['Keine Grüsse und Wünsche möglich.', 'Keine Wünsche und Grüße möglich.'], true)
        ) {
            $streamInfo->setModerator(null)
                ->setShow(null)
                ->setGenre(null);
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='track']//span");
        if (1 === $nodeList->length) {
            $matches = [];
            if (preg_match('/^(.*) - ([^-]*)$/', $nodeList->item(0)->nodeValue, $matches)) {
                $streamInfo->setArtist(trim($matches[1]));
                $streamInfo->setTrack(trim($matches[2]));
            }
        }

        // fetch showtime
        $dayOfWeek = date('N');
        $nodeList = $xpath->query("(.//div[@class='sendeplan']//div[@class='day']//ul[@class='list'])[".$dayOfWeek.']//li[position()>2]');

        $lastModerator = null;
        $found = false;
        $startTime = null;
        $endTime = null;
        for ($i = 0; $i < $nodeList->length; ++$i) {
            $item = $nodeList->item($i)->firstChild;
            $moderator = $item->nodeValue;

            // if moderator changed since last loop run
            if ($lastModerator !== $moderator) {
                // and if we didn't find the on air mod until now
                if (false === $found) {
                    // set new value for start time
                    $startTime = new \DateTime(($i + 14).':00');
                }
                // or we did already find the on air mod and can stop here
                else {
                    break;
                }
            }

            if ('nowonair' === trim($item->getAttribute('class'))) {
                $found = true;
            }

            // if we have found the on air mod and are still running the for loop
            if (true === $found) {
                // then set the end time to the start time of this column (of the html) + 1
                $endTime = new \DateTime(($i + 14 + 1).':00');
            }

            $lastModerator = $moderator;
        }

        if (
            true === $found
            && $startTime instanceof \DateTime
            && $endTime instanceof \DateTime
        ) {
            $streamInfo->setShowStartTime($startTime);
            $streamInfo->setShowEndTime($endTime);
        }

        return $streamInfo;
    }

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getStreamUrl(string $streamName): string
    {
        return self::STREAM_URL;
    }
}

<?php

declare(strict_types=1);

namespace App\Stream;

final class MetalOnly extends AbstractRadioStream
{
    const RADIO_NAME = 'Metal Only';
    const URL = 'https://www.metal-only.de';
    // use page 'Impressum' because there is only text and the page should load quicker
    const URL_INFO_PATH = '/sendeplan.html';
    const STREAM_URL = 'http://server1.blitz-stream.de:4400/;';

    const METAL_ONLY = 'Metal Only';

    const AVAILABLE_STREAMS = [
        self::METAL_ONLY,
    ];

    protected function getHomepageUrl(): string
    {
        return self::URL;
    }

    protected function getStreamUrl(): string
    {
        return self::STREAM_URL;
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
            $dom = $this->getDomFetcher()->getHtmlDom(self::URL.self::URL_INFO_PATH);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new \DOMXPath($dom);

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='headline']");
        if (1 === $nodeList->length) {
            $matches = [];
            if (preg_match('/^(.*) ist ON AIR$/', $nodeList->item(0)->nodeValue, $matches)) {
                $this->setModerator(trim($matches[1]));
            }
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='sendung']//span");
        if (1 === $nodeList->length) {
            $this->setShow(trim($nodeList->item(0)->nodeValue));
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='gerne']//span");
        if (1 === $nodeList->length) {
            $this->setGenre(trim($nodeList->item(0)->nodeValue));
        }

        // if there is no show/moderator then it displays some default values
        if (
            'MetalHead' === $this->getModerator()
            && 'Mixed Metal' === $this->getGenre()
            && \in_array($this->getShow(), ['Keine Grüsse und Wünsche möglich.', 'Keine Wünsche und Grüße möglich.'], true)
        ) {
            $this->setModerator(null)
                ->setShow(null)
                ->setGenre(null);
        }

        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='data']//div[@class='streaminfo']//span[@class='track']//span");
        if (1 === $nodeList->length) {
            $matches = [];
            if (preg_match('/^(.*) - ([^-]*)$/', $nodeList->item(0)->nodeValue, $matches)) {
                $this->setArtist(trim($matches[1]));
                $this->setTrack(trim($matches[2]));
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
            // the time table starts at 14:00 so the first row (0) represents 14:00
            $currentHour = (14 + $i) % 24;
            $item = $nodeList->item($i)->firstChild;
            $moderator = $item->nodeValue;

            // if moderator changed since last loop run
            if ($lastModerator !== $moderator) {
                // and if we didn't find the on air mod until now
                if (false === $found) {
                    // set new value for start time
                    $startTime = new \DateTime($currentHour.':00');
                }
                // or we did already find the on air mod and can stop here
                else {
                    break;
                }
            }

            if (
                $item instanceof \DomElement
                && $item->hasAttribute('class')
                && 'nowonair' === trim($item->getAttribute('class'))
                && !\in_array(trim($moderator), ['', 'MetalHead'], true)
            ) {
                $found = true;
            }

            // if we have found the on air mod and are still running the for loop
            if (true === $found) {
                // then set the end time to the current hour + 1
                $endHour = ($currentHour + 1) % 24;
                $endTime = new \DateTime($endHour.':00');
            }

            $lastModerator = $moderator;
        }

        if (
            true === $found
            && $startTime instanceof \DateTime
            && $endTime instanceof \DateTime
        ) {
            $this->setShowStartTime($startTime);
            $this->setShowEndTime($endTime);
        }
    }
}

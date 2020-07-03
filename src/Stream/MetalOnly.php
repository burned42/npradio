<?php

declare(strict_types=1);

namespace App\Stream;

use DateTime;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class MetalOnly extends AbstractRadioStream
{
    private const RADIO_NAME = 'Metal Only';
    private const URL = 'https://www.metal-only.de';
    // use page 'Impressum' because there is only text and the page should load quicker
    private const URL_INFO_PATH = '/sendeplan.html';
    private const STREAM_URL = 'http://server1.blitz-stream.de:4400/';

    private const METAL_ONLY = 'Metal Only';

    private const AVAILABLE_STREAMS = [
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
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function updateInfo(): void
    {
        try {
            $dom = $this->getDomFetcher()->getHtmlDom(self::URL.self::URL_INFO_PATH);
        } catch (Exception $e) {
            throw new RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new DOMXPath($dom);

        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(".//div[@class='boxx onair']//div[@class='headline']");
        if (1 === $nodeList->length) {
            $matches = [];
            $node = $nodeList->item(0);
            if (!($node instanceof DOMNode)) {
                throw new RuntimeException('could not get DOMNode for parsing the moderator');
            }

            if (preg_match('/^(.*) ist ON AIR$/', $node->nodeValue, $matches)) {
                $moderator = trim($matches[1]);
                if (!empty($moderator)) {
                    $this->setModerator($moderator);
                }
            }
        }

        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(
            ".//div[@class='boxx onair']//div[@class='data']"
            ."//div[@class='streaminfo']//span[@class='sendung']//span"
        );
        if (1 === $nodeList->length) {
            $node = $nodeList->item(0);
            if (!($node instanceof DOMNode)) {
                throw new RuntimeException('could not get DOMNode for parsing the show');
            }

            $show = trim($node->nodeValue);
            if (!empty($show)) {
                $this->setShow($show);
            }
        }

        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(
            ".//div[@class='boxx onair']//div[@class='data']"
            ."//div[@class='streaminfo']//span[@class='gerne']//span"
        );
        if (1 === $nodeList->length) {
            $node = $nodeList->item(0);
            if (!($node instanceof DOMNode)) {
                throw new RuntimeException('could not get DOMNode for parsing the genre');
            }

            $genre = trim($node->nodeValue);
            if (!empty($genre)) {
                $this->setGenre($genre);
            }
        }

        // Check if there is just some default data set for the current show
        $defaultModerators = [
            'MetalHead',
            'frei',
        ];
        $defaultShowNames = [
            'Keine Grüsse und Wünsche möglich.',
            'Keine Wünsche und Grüße möglich.',
            'Keine GrÃ¼sse und WÃ¼nsche mÃ¶glich.',
        ];
        $defaultGenres = [
            'Mixed Metal',
        ];
        if (
            in_array($this->getModerator(), $defaultModerators, true)
            && in_array($this->getShow(), $defaultShowNames, true)
            && in_array($this->getGenre(), $defaultGenres, true)
        ) {
            $this->setModerator()
                ->setShow()
                ->setGenre();
        }

        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(
            ".//div[@class='boxx onair']//div[@class='data']"
            ."//div[@class='streaminfo']//span[@class='track']//span"
        );
        if (1 === $nodeList->length) {
            $matches = [];
            $node = $nodeList->item(0);
            if (!($node instanceof DOMNode)) {
                throw new RuntimeException('could not get DOMNode for parsing the artist and track');
            }

            if (preg_match('/^(.*) - ([^-]*)$/', $node->nodeValue, $matches)) {
                $this->setArtist(trim($matches[1]));
                $this->setTrack(trim($matches[2]));
            }
        }

        // fetch showtime
        $dayOfWeek = date('N');
        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(
            "(.//div[@class='sendeplan']//div[@class='day']"
            ."//ul[@class='list'])[".$dayOfWeek.']//li[position()>2]'
        );

        $lastModerator = null;
        $found = false;
        $startTime = null;
        $endTime = null;
        for ($i = 0; $i < $nodeList->length; ++$i) {
            // the time table starts at 14:00 so the first row (0) represents 14:00
            $currentHour = (14 + $i) % 24;
            $node = $nodeList->item($i);
            if (!($node instanceof DOMNode)) {
                throw new RuntimeException('could not get DOMNode for parsing the moderator');
            }
            /** @var DOMNode $item */
            $item = $node->firstChild;
            $moderator = $item->nodeValue;

            // if moderator changed since last loop run
            if ($lastModerator !== $moderator) {
                // and if we didn't find the on air mod until now
                if (false === $found) {
                    // set new value for start time
                    $startTime = new DateTime($currentHour.':00');
                } else {
                    // or we did already find the on air mod and can stop here
                    break;
                }
            }

            if (
                $item instanceof DOMElement
                && $item->hasAttribute('class')
                && 'nowonair' === trim($item->getAttribute('class'))
                && !in_array(trim($moderator), ['', 'MetalHead'], true)
            ) {
                $found = true;
            }

            // if we have found the on air mod and are still running the for loop
            if (true === $found) {
                // then set the end time to the current hour + 1
                $endHour = ($currentHour + 1) % 24;
                $endTime = new DateTime($endHour.':00');
            }

            $lastModerator = $moderator;
        }

        if (
            true === $found
            && $startTime instanceof DateTime
            && $endTime instanceof DateTime
        ) {
            $this->setShowStartTime($startTime);
            $this->setShowEndTime($endTime);
        }
    }
}

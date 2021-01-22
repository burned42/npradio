<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use DateTimeImmutable;
use DateTimeInterface;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class MetalOnly extends AbstractRadioStream
{
    private const RADIO_NAME = 'Metal Only';
    private const URL = 'https://www.metal-only.de';
    // use page 'Impressum' because there is only text and the page should load quicker
    private const URL_INFO_PATH = '/sendeplan.html';
    private const STREAM_URL = 'https://metal-only.streampanel.cloud/stream';

    private const METAL_ONLY = 'Metal Only';

    private const AVAILABLE_STREAMS = [
        self::METAL_ONLY,
    ];

    public function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    /**
     * @throws Exception
     */
    public function getStreamInfo(string $streamName): StreamInfo
    {
        if (!in_array($streamName, $this->getAvailableStreams(), true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        $streamInfo = new StreamInfo(
            self::RADIO_NAME,
            $streamName,
            self::URL,
            self::STREAM_URL,
        );

        try {
            $streamInfo = $this->addTrackAndShowInfo($streamInfo);
        } catch (Throwable) {
        }

        return $streamInfo;
    }

    /**
     * @throws Exception
     */
    public function addTrackAndShowInfo(StreamInfo $streamInfo): StreamInfo
    {
        try {
            $dom = $this->getHttpDataFetcher()->getHtmlDom(self::URL.self::URL_INFO_PATH);
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
                    $streamInfo->moderator = $moderator;
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
                $streamInfo->show = $show;
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
                $streamInfo->genre = $genre;
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
            in_array($streamInfo->moderator, $defaultModerators, true)
            && in_array($streamInfo->show, $defaultShowNames, true)
            && in_array($streamInfo->genre, $defaultGenres, true)
        ) {
            $streamInfo->moderator = null;
            $streamInfo->show = null;
            $streamInfo->genre = null;
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
                $streamInfo->artist = trim($matches[1]);
                $streamInfo->track = trim($matches[2]);
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
                    $startTime = new DateTimeImmutable($currentHour.':00');
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
                $endTime = new DateTimeImmutable($endHour.':00');
            }

            $lastModerator = $moderator;
        }

        if (
            true === $found
            && $startTime instanceof DateTimeInterface
            && $endTime instanceof DateTimeInterface
        ) {
            $streamInfo->showStartTime = $startTime;
            $streamInfo->showEndTime = $endTime;
        }

        return $streamInfo;
    }
}

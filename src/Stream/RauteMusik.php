<?php

declare(strict_types=1);

namespace App\Stream;

use DateTimeImmutable;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class RauteMusik extends AbstractRadioStream
{
    private const RADIO_NAME = 'RauteMusik';
    private const BASE_URL = 'https://www.rautemusik.fm/';
    private const SHOW_INFO_URL = self::BASE_URL.'radio/sendeplan/';

    private const MAIN = 'RauteMusik Main';
    private const CLUB = 'RauteMusik Club';
    private const CHRISTMAS = 'RauteMusik Christmas';
    private const HAPPYHARDCORE = 'RauteMusik HappyHardcore';
    private const HARDER = 'RauteMusik HardeR';
    private const HOUSE = 'RauteMusik House';
    private const ROCK = 'RauteMusik Rock';
    private const TECHHOUSE = 'RauteMusik TechHouse';
    private const WACKENRADIO = 'Wacken Radio';
    private const WEIHNACHTEN = 'RauteMusik Weihnachten';

    private const AVAILABLE_STREAMS = [
        self::MAIN,
        self::CLUB,
        self::CHRISTMAS,
        self::HAPPYHARDCORE,
        self::HARDER,
        self::HOUSE,
        self::ROCK,
        self::TECHHOUSE,
        self::WACKENRADIO,
        self::WEIHNACHTEN,
    ];

    private function getStreamNameForUrl(string $streamName): string
    {
        return strtolower(
            str_replace(
                ['RauteMusik', ' '],
                '',
                $streamName
            )
        );
    }

    protected function getHomepageUrl(string $streamName): string
    {
        return self::BASE_URL.$this->getStreamNameForUrl($streamName);
    }

    protected function getStreamUrl(string $streamName): string
    {
        return 'http://'.$this->getStreamNameForUrl($streamName).'-high.rautemusik.fm';
    }

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
            $this->getHomepageUrl($streamName),
            $this->getStreamUrl($streamName),
        );

        $streamInfo = $this->addTrackInfo($streamInfo);
        $streamInfo = $this->addShowInfo($streamInfo);

        return $streamInfo;
    }

    private function addTrackInfo(StreamInfo $streamInfo): StreamInfo
    {
        try {
            $url = self::BASE_URL.$this->getStreamNameForUrl($streamInfo->streamName);
            $dom = $this->getDomFetcher()->getHtmlDom($url);
        } catch (Exception $e) {
            throw new RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new DOMXPath($dom);
        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(
            ".//li[@class='current']//p[@class='title']"
            ." | .//li[@class='current']//p[@class='artist']"
        );

        /** @var DOMNode $node */
        foreach ($nodeList as $node) {
            if (!($node->attributes instanceof DOMNamedNodeMap)) {
                throw new RuntimeException('could not find DOMNamedNodeMap for parsing artist and title');
            }
            $classNode = $node->attributes->getNamedItem('class');
            if (!($classNode instanceof DOMNode)) {
                throw new RuntimeException('could not find DOMNode for parsing artist and title');
            }

            $class = $classNode->nodeValue;
            if ('artist' === $class) {
                $streamInfo->artist = trim($node->nodeValue);
            } elseif ('title' === $class) {
                $streamInfo->track = trim($node->nodeValue);
            }
        }

        return $streamInfo;
    }

    /**
     * @throws Exception
     */
    private function addShowInfo(StreamInfo $streamInfo): StreamInfo
    {
        try {
            $url = self::SHOW_INFO_URL.$this->getStreamNameForUrl($streamInfo->streamName);
            $dom = $this->getDomFetcher()->getHtmlDom($url);
        } catch (Exception $e) {
            throw new RuntimeException('could not get html dom: '.$e->getMessage());
        }

        $xpath = new DOMXPath($dom);
        /** @var DOMNodeList<DOMNode> $nodeList */
        $nodeList = $xpath->query(".//tr[@class='current']//td");

        $numNodes = $nodeList->length;
        if ($numNodes >= 1) {
            $matches = [];
            $node = $nodeList->item(0);
            if (!($node instanceof DOMNode)) {
                throw new RuntimeException('could not get DOMNode for parsing show start and end time');
            }

            if (preg_match('/^(\d{2}:\d{2}) - (\d{2}:\d{2}) Uhr$/', $node->nodeValue, $matches)) {
                $streamInfo->showStartTime = new DateTimeImmutable($matches[1]);
                $streamInfo->showEndTime = new DateTimeImmutable($matches[2]);
            }

            if ($numNodes >= 2) {
                $node = $nodeList->item(1);
                if (!($node instanceof DOMNode)) {
                    throw new RuntimeException('could not get DOMNode for parsing the show');
                }

                $streamInfo->show = trim($node->nodeValue);

                if ($numNodes >= 3) {
                    $node = $nodeList->item(2);
                    if (!($node instanceof DOMNode)) {
                        throw new RuntimeException('could not get DOMNode for parsing the moderator');
                    }

                    $streamInfo->moderator = preg_replace('/\s+/', ' ', trim($node->nodeValue));
                }
            }
        }

        return $streamInfo;
    }
}

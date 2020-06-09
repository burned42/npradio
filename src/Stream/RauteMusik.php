<?php

declare(strict_types=1);

namespace App\Stream;

use DateTime;
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

    private function getStreamNameForUrl(): string
    {
        return strtolower(
            str_replace(
                ['RauteMusik', ' '],
                '',
                $this->getStreamName()
            )
        );
    }

    protected function getHomepageUrl(): string
    {
        return self::BASE_URL.$this->getStreamNameForUrl();
    }

    protected function getStreamUrl(): string
    {
        return 'http://'.$this->getStreamNameForUrl().'-high.rautemusik.fm';
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
        $this->updateTrackInfo();
        $this->updateShowInfo();
    }

    /**
     * @throws RuntimeException
     */
    private function updateTrackInfo(): void
    {
        try {
            $dom = $this->getDomFetcher()->getHtmlDom(self::BASE_URL.$this->getStreamNameForUrl());
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
                $this->setArtist(trim($node->nodeValue));
            } elseif ('title' === $class) {
                $this->setTrack(trim($node->nodeValue));
            }
        }
    }

    /**
     * @throws RuntimeException
     * @throws Exception
     */
    private function updateShowInfo(): void
    {
        try {
            $dom = $this->getDomFetcher()->getHtmlDom(self::SHOW_INFO_URL.$this->getStreamNameForUrl());
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
                $this->setShowStartTime(new DateTime($matches[1]));
                $this->setShowEndTime(new DateTime($matches[2]));
            }

            if ($numNodes >= 2) {
                $node = $nodeList->item(1);
                if (!($node instanceof DOMNode)) {
                    throw new RuntimeException('could not get DOMNode for parsing the show');
                }

                $this->setShow(trim($node->nodeValue));

                if ($numNodes >= 3) {
                    $node = $nodeList->item(2);
                    if (!($node instanceof DOMNode)) {
                        throw new RuntimeException('could not get DOMNode for parsing the moderator');
                    }

                    $this->setModerator(
                        preg_replace('/\s+/', ' ', trim($node->nodeValue))
                    );
                }
            }
        }
    }
}

<?php

namespace Burned\NPRadio;

require_once 'RadioStreamInterface.php';
require_once 'RadioInfo.php';

class RauteMusik implements RadioStreamInterface
{
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

    private $streamName;
    private $radioInfo;
    private $trackInfoUrl;
    private $showInfoUrl;

    public function __construct($streamName)
    {
        if (!in_array($streamName, self::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given');
        }

        $this->streamName = $streamName;

        $this->trackInfoUrl = self::BASE_URL . strtolower($this->streamName);
        $this->showInfoUrl = self::SHOW_INFO_URL . strtolower($this->streamName);

        $this->radioInfo = new RadioInfo();
        $this->radioInfo->setStreamName('#Musik.' . $this->streamName);
    }

    public function getInfo(): RadioInfo
    {
        $this->fetchTrackInfo();
        $this->fetchShowInfo();

        return $this->radioInfo;
    }

    private function fetchTrackInfo()
    {
        $dom = new \DOMDocument();
        // sadly I haven't found a better solution than ignoring any errors that
        // might occur, because the internet is broken, right?
        @$dom->loadHTMLFile($this->trackInfoUrl);
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
        $dom = new \DOMDocument();
        // sadly I haven't found a better solution than ignoring any errors that
        // might occur, because the internet is broken, right?
        @$dom->loadHTMLFile($this->showInfoUrl);
        $xpath = new \DOMXPath($dom);
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->query(".//tr[@class='current']//td");

        $matches = [];
        if (preg_match('/^([0-9]{2}:[0-9]{2}) - ([0-9]{2}:[0-9]{2}) Uhr$/', $nodeList->item(0)->nodeValue, $matches)) {
            $this->radioInfo->setShowStartTime(new \DateTime($matches[1]));
            $this->radioInfo->setShowEndTime(new \DateTime($matches[2]));
        }

        $this->radioInfo->setShow(trim($nodeList->item(1)->nodeValue));
        $this->radioInfo->setModerator(trim($nodeList->item(2)->nodeValue));
    }
}
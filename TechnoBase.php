<?php
/**
 * Created by PhpStorm.
 * User: burned
 * Date: 15.10.16
 * Time: 19:34
 */

namespace Burned\NPRadio;


class TechnoBase implements RadioStreamInterface
{
    const URL = 'http://tray.technobase.fm/radio.xml';

    const TECHNOBASE = 'TechnoBase';
    const HOUSETIME = 'HouseTime';
    const HARDBASE = 'HardBase';
    const TRANCEBASE = 'TranceBase';
    const CORETIME = 'CoreTime';
    const CLUBTIME = 'ClubTime';
    const TEATIME = 'TeaTime';

    const AVAILABLE_STREAMS = [
        self::TECHNOBASE,
        self::HOUSETIME,
        self::HARDBASE,
        self::TRANCEBASE,
        self::CORETIME,
        self::CLUBTIME,
        self::TEATIME
    ];

    private $streamName;

    private $moderator = null;
    private $show = null;
    private $genre = null;
    private $artist = null;
    private $track = null;
    private $starttime = null;
    private $endtime = null;

    public function __construct($streamName)
    {
        if (!in_array($streamName, self::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given');
        }

        $this->streamName = $streamName;
    }

    private function fetchInfo()
    {
        $xml = file_get_contents(self::URL);

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        $streamInfoNode = null;

        /** @var \DOMNode $weAreOneNode */
        foreach ($dom->childNodes as $weAreOneNode) {
            if ($weAreOneNode->nodeName === 'weareone') {
                /** @var \DOMNode $radioNode */
                foreach ($weAreOneNode->childNodes as $radioNode) {
                    if ($radioNode->nodeName === 'radio') {
                        /** @var \DOMNode $streamNode */
                        foreach ($radioNode->childNodes as $streamNode) {
                            if (
                                $streamNode->nodeName === 'name'
                                && $streamNode->nodeValue === $this->streamName
                            ) {
                                $streamInfoNode = $radioNode;
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        if (!is_null($streamInfoNode)) {
            /** @var \DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $infos = [
                    'moderator' => 'moderator',
                    'show' => 'show',
                    'genre' => 'style',
                    'artist' => 'artist',
                    'track' => 'song',
                    'starttime' => 'starttime',
                    'endtime' => 'endtime'
                ];
                foreach ($infos as $mapping => $info) {
                    if ($childNode->nodeName === $info) {
                        if (in_array($info, ['starttime', 'endtime'])) {
                            $this->$mapping = str_pad($childNode->nodeValue, 2, '0', STR_PAD_LEFT) . ':00';
                        }
                        else {
                            $this->$mapping = $childNode->nodeValue;
                        }
                    }
                }
            }
        }
    }

    public function getInfo(): array
    {
        $this->fetchInfo();

        return [
            'moderator' => $this->moderator,
            'show' => $this->show,
            'genre' => $this->genre,
            'artist' => $this->artist,
            'track' => $this->track,
            'starttime' => $this->starttime,
            'endtime' => $this->endtime
        ];
    }
}

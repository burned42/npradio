<?php

namespace Burned\NPRadio;

require_once 'RadioStreamInterface.php';
require_once 'RadioInfo.php';

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
    private $radioInfo;

    public function __construct($streamName)
    {
        if (!in_array($streamName, self::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given');
        }

        $this->streamName = $streamName;

        $this->radioInfo = new RadioInfo();
        $this->radioInfo->setStreamName($this->streamName);
    }

    public function getInfo(): RadioInfo
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
            $infos = [
                'setModerator' => 'moderator',
                'setShow' => 'show',
                'setGenre' => 'style',
                'setArtist' => 'artist',
                'setTrack' => 'song',
                'setShowStartTime' => 'starttime',
                'setShowEndTime' => 'endtime'
            ];

            /** @var \DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = $childNode->nodeValue;
                if (!empty(trim($nodeValue))) {
                    foreach ($infos as $setter => $info) {
                        if ($childNode->nodeName === $info) {
                            if (in_array($info, ['starttime', 'endtime'])) {
                                $this->radioInfo->$setter(
                                    new \DateTime(
                                        str_pad($nodeValue, 2, '0', STR_PAD_LEFT) . ':00'
                                    )
                                );
                            } else {
                                $this->radioInfo->$setter((string) $nodeValue);
                            }
                        }
                    }
                }
            }
        }

        return $this->radioInfo;
    }
}

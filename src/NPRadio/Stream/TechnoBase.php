<?php

namespace NPRadio\Stream;

class TechnoBase extends RadioStream
{
    const RADIO_NAME = 'TechnoBase';

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

    protected function getHomepageUrl(string $streamName): string
    {
        if (!in_array($streamName, self::AVAILABLE_STREAMS)) {
            throw new \InvalidArgumentException('invalid stream name given: ' . $streamName);
        }

        return 'https://www.' . strtolower($streamName) . '.fm';
    }

    public function getInfo(string $streamName): StreamInfo
    {
        $streamInfo = $this->getStreamInfo($streamName);

        try {
            $dom = $this->domFetcher->getXmlDom(self::URL);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get xml dom: ' . $e->getMessage());
        }

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
                                && $streamNode->nodeValue === $streamName
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
                'setModerator'     => 'moderator',
                'setShow'          => 'show',
                'setGenre'         => 'style',
                'setArtist'        => 'artist',
                'setTrack'         => 'song',
                'setShowStartTime' => 'starttime',
                'setShowEndTime'   => 'endtime'
            ];

            /** @var \DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = $childNode->nodeValue;
                if (!empty(trim($nodeValue)) || $nodeValue === '0') {
                    foreach ($infos as $setter => $info) {
                        if ($childNode->nodeName === $info) {
                            if (in_array($info, ['starttime', 'endtime'])) {
                                $streamInfo->$setter(
                                    new \DateTime(
                                        str_pad($nodeValue, 2, '0', STR_PAD_LEFT) . ':00'
                                    )
                                );
                            } else {
                                $streamInfo->$setter((string)$nodeValue);
                            }
                        }
                    }
                }
            }
        }

        return $streamInfo;
    }
}

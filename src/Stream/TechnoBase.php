<?php

declare(strict_types=1);

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
        self::TEATIME,
    ];

    /**
     * @param string $streamName
     *
     * @return string
     */
    protected function getHomepageUrl(string $streamName): string
    {
        return 'https://www.'.strtolower($streamName).'.fm';
    }

    /**
     * @param string $streamName
     *
     * @return StreamInfo
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getInfo(string $streamName): StreamInfo
    {
        $streamInfo = $this->getStreamInfo($streamName);

        try {
            $dom = $this->domFetcher->getXmlDom(self::URL);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get xml dom: '.$e->getMessage());
        }

        $streamInfoNode = null;

        /** @var \DOMNode $weAreOneNode */
        foreach ($dom->childNodes as $weAreOneNode) {
            if ('weareone' === $weAreOneNode->nodeName) {
                /** @var \DOMNode $radioNode */
                foreach ($weAreOneNode->childNodes as $radioNode) {
                    if ('radio' === $radioNode->nodeName) {
                        /** @var \DOMNode $streamNode */
                        foreach ($radioNode->childNodes as $streamNode) {
                            if (
                                'name' === $streamNode->nodeName
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

        if (null !== $streamInfoNode) {
            $infos = [
                'setModerator' => 'moderator',
                'setShow' => 'show',
                'setGenre' => 'style',
                'setArtist' => 'artist',
                'setTrack' => 'song',
                'setShowStartTime' => 'starttime',
                'setShowEndTime' => 'endtime',
            ];

            /** @var \DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = $childNode->nodeValue;
                if ('0' === $nodeValue || !empty(trim($nodeValue))) {
                    foreach ($infos as $setter => $info) {
                        if ($childNode->nodeName === $info) {
                            if (\in_array($info, ['starttime', 'endtime'])) {
                                $streamInfo->$setter(
                                    new \DateTime(
                                        str_pad($nodeValue, 2, '0', STR_PAD_LEFT).':00'
                                    )
                                );
                            } else {
                                $streamInfo->$setter(trim($nodeValue));
                            }
                        }
                    }
                }
            }
        }

        $showStartTime = $streamInfo->getShowStartTime();
        $showEndTime = $streamInfo->getShowEndTime();
        if (
            $showStartTime instanceof \DateTime
            && $showEndTime instanceof \DateTime
            && $showStartTime->format('H:i') === $showEndTime->format('H:i')
        ) {
            $streamInfo->setShowStartTime(null);
            $streamInfo->setShowEndTime(null);
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
        //return 'http://listen.' . strtolower($streamName) . '.fm/tunein-dsl-pls';

        $fileName = '';
        $ucLetters = range('A', 'Z');
        $streamNameLength = \strlen($streamName);
        for ($i = 0; $i < $streamNameLength; ++$i) {
            if (\in_array($streamName[$i], $ucLetters, true)) {
                $fileName .= strtolower($streamName[$i]);
            }
        }

        return 'http://lw2.mp3.tb-group.fm/'.$fileName.'.mp3';
    }
}
<?php

declare(strict_types=1);

namespace App\Stream;

final class TechnoBase extends AbstractRadioStream
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

    protected function getHomepageUrl(): string
    {
        return 'https://www.'.strtolower($this->getStreamName()).'.fm';
    }

    protected function getStreamUrl(): string
    {
        //return 'http://listen.' . strtolower($streamName) . '.fm/tunein-dsl-pls';

        $shortName = preg_replace('/[^A-Z]/', '', $this->getStreamName());
        $fileName = strtolower($shortName);

        return 'http://lw2.mp3.tb-group.fm/'.$fileName.'.mp3';
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
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function updateInfo()
    {
        try {
            $dom = $this->getDomFetcher()->getXmlDom(self::URL);
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
                                && $streamNode->nodeValue === $this->getStreamName()
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
                                $this->$setter(
                                    new \DateTime(
                                        str_pad($nodeValue, 2, '0', STR_PAD_LEFT).':00'
                                    )
                                );
                            } else {
                                $this->$setter(trim($nodeValue));
                            }
                        }
                    }
                }
            }
        }

        $showStartTime = $this->getShowStartTime();
        $showEndTime = $this->getShowEndTime();
        if (
            $showStartTime instanceof \DateTime
            && $showEndTime instanceof \DateTime
            && $showStartTime->format('H:i') === $showEndTime->format('H:i')
        ) {
            $this->setShowStartTime(null);
            $this->setShowEndTime(null);
        }
    }
}

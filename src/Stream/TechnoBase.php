<?php

declare(strict_types=1);

namespace App\Stream;

use DateTime;
use DOMNode;
use Exception;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

final class TechnoBase extends AbstractRadioStream
{
    private const RADIO_NAME = 'TechnoBase.FM';
    private const URL = 'http://tray.technobase.fm/radio.xml';

    private const TECHNOBASE = 'TechnoBase.FM';
    private const HOUSETIME = 'HouseTime.FM';
    private const HARDBASE = 'HardBase.FM';
    private const TRANCEBASE = 'TranceBase.FM';
    private const CORETIME = 'CoreTime.FM';
    private const CLUBTIME = 'ClubTime.FM';
    private const TEATIME = 'TeaTime.FM';

    /*
     * Values are the filenames for the stream urls
     */
    private const AVAILABLE_STREAMS = [
        self::TECHNOBASE => 'tb',
        self::HOUSETIME => 'ht',
        self::HARDBASE => 'hb',
        self::TRANCEBASE => 'trb',
        self::CORETIME => 'ct',
        self::CLUBTIME => 'clt',
        self::TEATIME => 'tt',
    ];

    private function getStreamNameWithoutSuffix(): string
    {
        return substr($this->getStreamName(), 0, -3);
    }

    protected function getHomepageUrl(): string
    {
        return 'https://www.'.strtolower($this->getStreamName());
    }

    protected function getStreamUrl(): string
    {
        $fileName = self::AVAILABLE_STREAMS[$this->getStreamName()];

        return 'http://mp3.stream.tb-group.fm/'.$fileName.'.mp3';
    }

    public static function getAvailableStreams(): array
    {
        return array_keys(self::AVAILABLE_STREAMS);
    }

    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function updateInfo(): void
    {
        try {
            $dom = $this->getDomFetcher()->getXmlDom(self::URL);
        } catch (Exception $e) {
            throw new RuntimeException('could not get xml dom: '.$e->getMessage());
        }

        $streamInfoNode = null;

        /** @var DOMNode $weAreOneNode */
        foreach ($dom->childNodes as $weAreOneNode) {
            if ('weareone' === $weAreOneNode->nodeName) {
                /** @var DOMNode $radioNode */
                foreach ($weAreOneNode->childNodes as $radioNode) {
                    if ('radio' === $radioNode->nodeName) {
                        /** @var DOMNode $streamNode */
                        foreach ($radioNode->childNodes as $streamNode) {
                            if (
                                'name' === $streamNode->nodeName
                                && $streamNode->nodeValue === $this->getStreamNameWithoutSuffix()
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

            /** @var DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = $childNode->nodeValue;
                if ('0' === $nodeValue || !empty(trim($nodeValue))) {
                    foreach ($infos as $setter => $info) {
                        if ($childNode->nodeName === $info) {
                            if (in_array($info, ['starttime', 'endtime'])) {
                                $this->$setter(
                                    new DateTime(
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
            $showStartTime instanceof DateTime
            && $showEndTime instanceof DateTime
            && $showStartTime->format('H:i') === $showEndTime->format('H:i')
        ) {
            $this->setShowStartTime();
            $this->setShowEndTime();
        }
    }
}

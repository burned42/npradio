<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use DateTimeImmutable;
use DateTimeInterface;
use DOMNode;
use Exception;
use InvalidArgumentException;
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

    private function getStreamNameWithoutSuffix(string $streamName): string
    {
        return substr($streamName, 0, -3);
    }

    protected function getHomepageUrl(string $streamName): string
    {
        return 'https://www.'.strtolower($streamName);
    }

    protected function getStreamUrl(string $streamName): string
    {
        $fileName = self::AVAILABLE_STREAMS[$streamName];

        return 'https://mp3.stream.tb-group.fm/'.$fileName.'.mp3';
    }

    public function getAvailableStreams(): array
    {
        return array_keys(self::AVAILABLE_STREAMS);
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
                                && $streamNode->nodeValue === $this->getStreamNameWithoutSuffix($streamName)
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
                'moderator' => 'moderator',
                'show' => 'show',
                'genre' => 'style',
                'artist' => 'artist',
                'track' => 'song',
                'showStartTime' => 'starttime',
                'showEndTime' => 'endtime',
            ];

            /** @var DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = $childNode->nodeValue;
                if ('0' === $nodeValue || !empty(trim($nodeValue))) {
                    foreach ($infos as $property => $info) {
                        if ($childNode->nodeName === $info) {
                            if (in_array($info, ['starttime', 'endtime'])) {
                                $streamInfo->$property = new DateTimeImmutable(
                                    str_pad($nodeValue, 2, '0', STR_PAD_LEFT).':00'
                                );
                            } else {
                                $streamInfo->$property = trim($nodeValue);
                            }
                        }
                    }
                }
            }
        }

        $showStartTime = $streamInfo->showStartTime;
        $showEndTime = $streamInfo->showEndTime;
        if (
            $showStartTime instanceof DateTimeInterface
            && $showEndTime instanceof DateTimeInterface
            && $showStartTime->format('H:i') === $showEndTime->format('H:i')
        ) {
            $streamInfo->showStartTime = null;
            $streamInfo->showEndTime = null;
        }

        return $streamInfo;
    }
}

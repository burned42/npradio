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
use function Sentry\captureException;
use Throwable;

final class TechnoBase extends AbstractRadioStream
{
    private const RADIO_NAME = 'TechnoBase.FM';
    private const URL = 'https://tray.technobase.fm/radio.xml';

    private const TECHNOBASE = 'TechnoBase.FM';
    private const HOUSETIME = 'HouseTime.FM';
    private const HARDBASE = 'HardBase.FM';
    private const TRANCEBASE = 'TranceBase.FM';
    private const CORETIME = 'CoreTime.FM';
    private const CLUBTIME = 'ClubTime.FM';
    private const TEATIME = 'TeaTime.FM';
    private const REPLAY = 'Replay.FM';

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
        self::REPLAY => 'rp',
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
            $streamInfo = $this->addTrackAndShowInfo($streamName, $streamInfo);
        } catch (Throwable $t) {
            captureException($t);
        }

        return $streamInfo;
    }

    public function addTrackAndShowInfo(string $streamName, StreamInfo $streamInfo): StreamInfo
    {
        try {
            $dom = $this->getHttpDataFetcher()->getXmlDom(self::URL);
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
            /** @var DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = trim($childNode->nodeValue);
                if ('0' !== $nodeValue && empty($nodeValue)) {
                    continue;
                }

                switch ($childNode->nodeName) {
                    case 'moderator':
                        $streamInfo->moderator = $nodeValue;
                        break;
                    case 'show':
                        $streamInfo->show = $nodeValue;
                        break;
                    case 'style':
                        $streamInfo->genre = $nodeValue;
                        break;
                    case 'artist':
                        $streamInfo->artist = $nodeValue;
                        break;
                    case 'song':
                        $streamInfo->track = $nodeValue;
                        break;
                    case 'starttime':
                        $streamInfo->showStartTime = new DateTimeImmutable(
                            str_pad($nodeValue, 2, '0', STR_PAD_LEFT).':00'
                        );
                        break;
                    case 'endtime':
                        $streamInfo->showEndTime = new DateTimeImmutable(
                            str_pad($nodeValue, 2, '0', STR_PAD_LEFT).':00'
                        );
                        break;
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

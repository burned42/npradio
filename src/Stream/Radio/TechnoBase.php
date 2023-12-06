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
use Override;
use RuntimeException;
use Throwable;

use function Sentry\captureException;

final class TechnoBase extends AbstractRadioStream
{
    private const string RADIO_NAME = 'TechnoBase.FM';
    private const string URL = 'https://tray.technobase.fm/radio.xml';

    private const string TECHNOBASE = 'TechnoBase.FM';
    private const string HOUSETIME = 'HouseTime.FM';
    private const string HARDBASE = 'HardBase.FM';
    private const string TRANCEBASE = 'TranceBase.FM';
    private const string CORETIME = 'CoreTime.FM';
    private const string CLUBTIME = 'ClubTime.FM';
    private const string TEATIME = 'TeaTime.FM';
    private const string REPLAY = 'Replay.FM';
    /**
     * Values are the filenames for the stream urls.
     *
     * @var string[]
     */
    private const array AVAILABLE_STREAMS = [
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

    private function getHomepageUrl(string $streamName): string
    {
        return 'https://www.'.strtolower($streamName);
    }

    private function getStreamUrl(string $streamName): string
    {
        $fileName = self::AVAILABLE_STREAMS[$streamName];

        return 'https://mp3.stream.tb-group.fm/'.$fileName.'.mp3';
    }

    #[Override]
    public function getAvailableStreams(): array
    {
        return array_keys(self::AVAILABLE_STREAMS);
    }

    #[Override]
    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    /**
     * @throws Exception
     */
    #[Override]
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

        if ($streamInfoNode instanceof DOMNode) {
            /** @var DOMNode $childNode */
            foreach ($streamInfoNode->childNodes as $childNode) {
                $nodeValue = trim($childNode->nodeValue ?? '');
                if ('' === $nodeValue) {
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

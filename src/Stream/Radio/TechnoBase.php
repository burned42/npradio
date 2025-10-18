<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use DateTimeImmutable;
use DateTimeInterface;
use Dom\Element;
use Exception;
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
        $this->assertValidStreamName($streamName);

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

        $radioNodes = $dom->querySelectorAll('weareone > radio');
        foreach ($radioNodes as $radioNode) {
            $name = $radioNode->querySelector('name')?->textContent;
            if ($this->getStreamNameWithoutSuffix($streamName) === $name) {
                $streamInfoNode = $radioNode;
                break;
            }
        }

        if (!($streamInfoNode instanceof Element)) {
            return $streamInfo;
        }

        $streamInfo->moderator = $this->getTextContent($streamInfoNode, 'moderator');
        $streamInfo->show = $this->getTextContent($streamInfoNode, 'show');
        $streamInfo->genre = $this->getTextContent($streamInfoNode, 'style');
        $streamInfo->artist = $this->getTextContent($streamInfoNode, 'artist');
        $streamInfo->track = $this->getTextContent($streamInfoNode, 'song');

        $startTime = $this->getTimeContent($streamInfoNode, 'starttime');
        $endTime = $this->getTimeContent($streamInfoNode, 'endtime');
        if (
            $startTime instanceof DateTimeInterface
            && $endTime instanceof DateTimeInterface
            && $startTime->format('H:i') !== $endTime->format('H:i')
        ) {
            $streamInfo->showStartTime = $startTime;
            $streamInfo->showEndTime = $endTime;
        }

        return $streamInfo;
    }

    private function getTextContent(Element $node, string $name): ?string
    {
        $content = trim(
            $node->querySelector($name)->textContent ?? '',
        );
        if ('' === $content) {
            return null;
        }

        return $content;
    }

    private function getTimeContent(Element $node, string $name): ?DateTimeInterface
    {
        $time = $this->getTextContent($node, $name);
        if (!is_string($time)) {
            return null;
        }

        return new DateTimeImmutable(
            str_pad($time, 2, '0', STR_PAD_LEFT).':00'
        );
    }
}

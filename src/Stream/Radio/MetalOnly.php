<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use DateTimeImmutable;
use DateTimeInterface;
use Dom\Element;
use Override;
use RuntimeException;
use Throwable;

use function Sentry\captureException;

final class MetalOnly extends AbstractRadioStream
{
    private const string RADIO_NAME = 'Metal Only';
    private const string URL = 'https://www.metal-only.de';
    private const string URL_INFO_PATH = '/sendeplan.html';
    private const string STREAM_URL = 'https://metal-only.streampanel.cloud/stream';

    private const string METAL_ONLY = 'Metal Only';
    /** @var string[] */
    private const array AVAILABLE_STREAMS = [
        self::METAL_ONLY,
    ];

    #[Override]
    public function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    #[Override]
    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    #[Override]
    public function getStreamInfo(string $streamName): StreamInfo
    {
        $this->assertValidStreamName($streamName);

        $streamInfo = new StreamInfo(
            self::RADIO_NAME,
            $streamName,
            self::URL,
            self::STREAM_URL,
        );

        try {
            $streamInfo = $this->addTrackAndShowInfo($streamInfo);
        } catch (Throwable $t) {
            captureException($t);
        }

        return $streamInfo;
    }

    public function addTrackAndShowInfo(StreamInfo $streamInfo): StreamInfo
    {
        $dom = $this->getHttpDataFetcher()->getHtmlDom(self::URL.self::URL_INFO_PATH);

        $onairText = $dom->querySelector('div.boxx.onair > div.headline')
            ?->textContent;
        if (preg_match('/^(.*) ist ON AIR$/', $onairText ?? '', $matches)) {
            $moderator = trim($matches[1]);
            if ('' !== $moderator) {
                $streamInfo->moderator = $moderator;
            }
        }

        $show = trim(
            $dom->querySelector('div.boxx.onair > div.data > div.streaminfo > span.sendung > span')
                ->textContent ?? ''
        );
        if ('' !== $show) {
            $streamInfo->show = $show;
        }

        $genre = trim(
            $dom->querySelector('div.boxx.onair > div.data > div.streaminfo > span.gerne > span')
                ->textContent ?? ''
        );
        if ('' !== $genre) {
            $streamInfo->genre = $genre;
        }

        // Check if there is just some default data set for the current show
        $defaultModerators = [
            'MetalHead',
            'frei',
        ];
        $defaultShowNames = [
            'Keine Grüsse und Wünsche möglich.',
            'Keine Wünsche und Grüße möglich.',
            'Keine GrÃ¼sse und WÃ¼nsche mÃ¶glich.',
        ];
        $defaultGenres = [
            'Mixed Metal',
        ];
        if (
            in_array($streamInfo->moderator, $defaultModerators, true)
            && in_array($streamInfo->show, $defaultShowNames, true)
            && in_array($streamInfo->genre, $defaultGenres, true)
        ) {
            $streamInfo->moderator = null;
            $streamInfo->show = null;
            $streamInfo->genre = null;
        }

        $songInfo = $dom->querySelector('div.boxx.onair > div.data > div.streaminfo > span.track > span')
            ->textContent ?? '';
        $matches = [];
        if (preg_match('/^(.*) - (.*)$/', $songInfo, $matches)) {
            $streamInfo->artist = trim($matches[1]);
            $streamInfo->track = trim($matches[2]);
        }

        // fetch showtime
        $dayOfWeek = date('N');
        $nodeList = $dom->querySelectorAll('div.sendeplan > div.day > ul.list')
            ->item(((int) $dayOfWeek) - 1)
            ?->querySelectorAll('li:not(:first-child):not(:nth-child(2))')
            ?? throw new RuntimeException('could not find stream plan for today');

        $lastModerator = null;
        $found = false;
        $startTime = null;
        $endTime = null;
        for ($i = 0; $i < $nodeList->length; ++$i) {
            // the time table starts at 14:00 so the first row (0) represents 14:00
            $currentHour = (14 + $i) % 24;
            $node = $nodeList->item($i)
                ?? throw new RuntimeException('could not get Element for parsing the moderator');
            $item = $node->firstChild
                ?? throw new RuntimeException('did not find expected child Element');
            $moderator = $item->textContent;

            // if moderator changed since last loop run
            if ($lastModerator !== $moderator) {
                // and if we didn't find the on air mod until now
                if (!$found) {
                    // set new value for start time
                    $startTime = new DateTimeImmutable($currentHour.':00');
                } else {
                    // or we did already find the on air mod and can stop here
                    break;
                }
            }

            if (
                $item instanceof Element
                && $item->hasAttribute('class')
                && 'nowonair' === trim($item->getAttribute('class') ?? '')
                && !in_array(trim($moderator ?? ''), ['', 'MetalHead'], true)
            ) {
                $found = true;
            }

            // if we have found the on air mod and are still running the for loop
            if ($found) {
                // then set the end time to the current hour + 1
                $endHour = ($currentHour + 1) % 24;
                $endTime = new DateTimeImmutable($endHour.':00');
            }

            $lastModerator = $moderator;
        }

        if (
            $found
            && $startTime instanceof DateTimeInterface
            && $endTime instanceof DateTimeInterface
        ) {
            $streamInfo->showStartTime = $startTime;
            $streamInfo->showEndTime = $endTime;
        }

        return $streamInfo;
    }
}

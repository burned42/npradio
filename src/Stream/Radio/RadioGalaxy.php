<?php

declare(strict_types=1);

namespace App\Stream\Radio;

use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class RadioGalaxy extends AbstractRadioStream
{
    private const RADIO_NAME = 'Radio Galaxy';

    private const MITTELFRANKEN = 'Radio Galaxy Mittelfranken';

    private const AVAILABLE_STREAMS = [
        self::MITTELFRANKEN,
    ];

    private const HOMEPAGE_URLS = [
        self::MITTELFRANKEN => 'https://www.galaxy-mittelfranken.de',
    ];

    private const STREAM_URLS = [
        self::MITTELFRANKEN => 'http://www.galaxyansbach.de:8000/live',
    ];

    private const INFO_URLS_BY_STREAM = [
        self::MITTELFRANKEN => 'https://www.galaxy-mittelfranken.de/wp-content/themes/radio-galaxy/tmp/1.json',
    ];

    public function getAvailableStreams(): array
    {
        return self::AVAILABLE_STREAMS;
    }

    public static function getRadioName(): string
    {
        return self::RADIO_NAME;
    }

    public function getStreamInfo(string $streamName): StreamInfo
    {
        if (!in_array($streamName, $this->getAvailableStreams(), true)) {
            throw new InvalidArgumentException('Invalid stream name given');
        }

        $streamInfo = new StreamInfo(
            self::RADIO_NAME,
            $streamName,
            self::HOMEPAGE_URLS[$streamName],
            self::STREAM_URLS[$streamName],
        );

        try {
            $url = self::INFO_URLS_BY_STREAM[$streamName];
            $data = json_decode($this->getDomFetcher()->getUrlContent($url), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!empty($data)) {
            if (array_key_exists('playlist', $data) && !empty($data['playlist'])) {
                $current = array_pop($data['playlist']);
                if (
                    is_array($current)
                    && array_key_exists('interpret', $current)
                    && array_key_exists('title', $current)
                ) {
                    $streamInfo->artist = $current['interpret'];
                    $streamInfo->track = $current['title'];
                }
            }

            if (
                array_key_exists('show', $data)
                && is_array($data['show'])
                && array_key_exists('title', $data['show'])
                && array_key_exists('desc', $data['show'])
                && !empty($data['show']['title'])
                && !empty($data['show']['desc'])
            ) {
                $streamInfo->moderator = preg_replace('/^mit /', '', trim($data['show']['desc']));
                $streamInfo->show = trim($data['show']['title']);
            }
        }

        return $streamInfo;
    }
}

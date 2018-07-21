<?php

declare(strict_types=1);

namespace NPRadio\Stream;

class RadioGalaxy extends AbstractRadioStream
{
    const RADIO_NAME = 'Radio Galaxy';

    const MITTELFRANKEN = 'Mittelfranken';

    const AVAILABLE_STREAMS = [
        self::MITTELFRANKEN,
    ];

    const HOMEPAGE_URLS = [
        self::MITTELFRANKEN => 'https://www.galaxy-mittelfranken.de',
    ];

    const STREAM_URLS = [
        self::MITTELFRANKEN => 'http://www.galaxyansbach.de:8000/live',
    ];

    const INFO_URLS_BY_STREAM = [
        self::MITTELFRANKEN => 'https://www.galaxy-mittelfranken.de/wp-content/themes/radio-galaxy/tmp/1.json',
    ];

    protected function getHomepageUrl(): string
    {
        return self::HOMEPAGE_URLS[$this->getStreamName()];
    }

    protected function getStreamUrl(): string
    {
        return self::STREAM_URLS[$this->getStreamName()];
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function updateInfo()
    {
        try {
            $url = self::INFO_URLS_BY_STREAM[$this->getStreamName()];
            $data = json_decode($this->domFetcher->getUrlContent($url), true);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get url content: '.$e->getMessage());
        }

        if (!empty($data)) {
            if (array_key_exists('playlist', $data) && !empty($data['playlist'])) {
                $current = array_pop($data['playlist']);
                if (
                    is_array($current)
                    && array_key_exists('interpret', $current)
                    && array_key_exists('title', $current)
                ) {
                    $this->setArtist($current['interpret']);
                    $this->setTrack($current['title']);
                }
            }

            if (
                array_key_exists('show', $data)
                && is_array($data['show'])
            ) {
                $show = null;
                if (
                    array_key_exists('title', $data['show'])
                    && !empty($data['show']['title'])
                ) {
                    $show = trim($data['show']['title']);

                    if (!in_array($show, ['Radio Galaxy am Vormittag', 'Radio Galaxy am Mittag', 'Radio Galaxy am Mittag / Nachmittag'])) {
                        $this->setShow($show);
                    }
                }

                if (
                    array_key_exists('desc', $data['show'])
                    && !empty($data['show']['desc'])
                ) {
                    $this->setModerator(preg_replace('/^mit /', '', trim($data['show']['desc'])));
                    $this->setShow($show);
                }
            }
        }
    }
}

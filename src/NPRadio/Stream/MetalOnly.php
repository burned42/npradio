<?php

namespace NPRadio\Stream;

class MetalOnly extends RadioStream
{
    const RADIO_NAME = 'MetalOnly';
    const URL = 'https://metal-only.de';

    const METAL_ONLY = 'MetalOnly';

    const AVAILABLE_STREAMS = [
        self::METAL_ONLY
    ];

    protected function getHomepageUrl(string $streamName): string
    {
        return self::URL;
    }

    public function getInfo(string $streamName): RadioInfo
    {
        $radioInfo = $this->getRadioInfo($streamName);

        try {
            $dom = $this->domFetcher->getHtmlDom(self::URL);
        } catch (\Exception $e) {
            throw new \RuntimeException('could not get html dom: ' . $e->getMessage());
        }

        $divs = $dom->getElementsByTagName('div');
        /** @var \DOMNode $div */
        foreach ($divs as $div) {
            if (preg_match('/Aktuell On Air: /', $div->nodeValue)) {
                /** @var \DOMNode $child */
                foreach ($div->childNodes as $child) {
                    $matches = [];
                    if (preg_match('/^Aktuell On Air: (.*)$/', $child->nodeValue, $matches)) {
                        $radioInfo->setModerator(trim($matches[1]));
                    } elseif (preg_match('/^Sendung: (.*)$/', $child->nodeValue, $matches)) {
                        $radioInfo->setShow(trim($matches[1]));
                    } elseif (preg_match('/^Genre: (.*)$/', $child->nodeValue, $matches)) {
                        $radioInfo->setGenre(trim($matches[1]));
                    } elseif (preg_match('/^Track: ([^-]*) - (.*)$/', $child->nodeValue, $matches)) {
                        $radioInfo->setArtist(trim($matches[1]));
                        $radioInfo->setTrack(trim($matches[2]));
                    }
                }
            }
        }

        return $radioInfo;
    }
}

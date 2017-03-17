<?php

namespace NPRadio;

class MetalOnly extends RadioStream
{
    const RADIO_NAME = 'MetalOnly';
    const URL = 'https://metal-only.de';

    protected function getHomepageUrl(): string
    {
        return self::URL;
    }

    public function getInfo(): RadioInfo
    {
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
                        $this->radioInfo->setModerator(trim($matches[1]));
                    } elseif (preg_match('/^Sendung: (.*)$/', $child->nodeValue, $matches)) {
                        $this->radioInfo->setShow(trim($matches[1]));
                    } elseif (preg_match('/^Genre: (.*)$/', $child->nodeValue, $matches)) {
                        $this->radioInfo->setGenre(trim($matches[1]));
                    } elseif (preg_match('/^Track: ([^-]*) - (.*)$/', $child->nodeValue, $matches)) {
                        $this->radioInfo->setArtist(trim($matches[1]));
                        $this->radioInfo->setTrack(trim($matches[2]));
                    }
                }
            }
        }

        return $this->radioInfo;
    }
}

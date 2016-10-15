<?php
/**
 * Created by PhpStorm.
 * User: burned
 * Date: 15.10.16
 * Time: 19:25
 */

namespace Burned\NPRadio;


class MetalOnly implements RadioStreamInterface
{
    const URL = 'http://metal-only.de';

    private $track = null;
    private $artist = null;
    private $genre = null;
    private $show = null;
    private $moderator = null;

    private function fetchInfo()
    {
        $dom = new \DOMDocument();
        // sadly I havn't found a better solution than ignoring any errors that
        // might occur, because the internet is broken, right?
        @$dom->loadHTMLFile(self::URL);

        $divs = $dom->getElementsByTagName('div');
        /** @var \DOMNode $div */
        foreach ($divs as $div) {
            if (preg_match('/Aktuell On Air: /', $div->nodeValue)) {
                /** @var \DOMNode $child */
                foreach ($div->childNodes as $child) {
                    $matches = [];
                    if (preg_match('/^Aktuell On Air: (.*)$/', $child->nodeValue, $matches)) {
                        $this->moderator = trim($matches[1]);
                    }
                    elseif (preg_match('/^Sendung: (.*)$/', $child->nodeValue, $matches)) {
                        $this->show = trim($matches[1]);
                    }
                    elseif (preg_match('/^Genre: (.*)$/', $child->nodeValue, $matches)) {
                        $this->genre = trim($matches[1]);
                    }
                    elseif (preg_match('/^Track: ([^-]*) - (.*)$/', $child->nodeValue, $matches)) {
                        $this->artist = trim($matches[1]);
                        $this->track = trim($matches[2]);
                    }
                }
            }
        }
    }

    public function getInfo(): array
    {
        $this->fetchInfo();

        return [
            'track' => $this->track,
            'artist' => $this->artist,
            'genre' => $this->genre,
            'show' => $this->show,
            'moderator' => $this->moderator
        ];
    }
}

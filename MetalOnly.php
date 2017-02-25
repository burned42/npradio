<?php

namespace Burned\NPRadio;

require_once 'RadioStreamInterface.php';
require_once 'RadioInfo.php';

class MetalOnly implements RadioStreamInterface
{
    const URL = 'https://metal-only.de';

    /** @var RadioInfo */
    private $radioInfo;

    public function __construct()
    {
        $this->radioInfo = new RadioInfo();
        $this->radioInfo->setStreamName('Metal Only');
    }

    public function getInfo(): RadioInfo
    {
        $dom = new \DOMDocument();
        // sadly I haven't found a better solution than ignoring any errors that
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

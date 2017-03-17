<?php

namespace NPRadio;

class HttpDomFetcher implements DomFetcher
{
    public function getXmlDom($url): \DOMDocument
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('invalid url given');
        }

        $xml = file_get_contents($url);
        if ($xml === false) {
            throw new \RuntimeException('could not fetch data from url "' . $url . '"');
        }

        $dom = new \DOMDocument();
        if ($dom->loadXML($xml) === false) {
            throw new \RuntimeException('could not parse xml data');
        }

        return $dom;
    }

    public function getHtmlDom($url): \DOMDocument
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('invalid url given');
        }

        $dom = new \DOMDocument();
        // sadly I haven't found a better solution than ignoring any errors that
        // might occur, because the internet is broken, right?
        if (@$dom->loadHTMLFile($url) === false) {
            throw new \RuntimeException('could not parse html data');
        }

        return $dom;
    }
}

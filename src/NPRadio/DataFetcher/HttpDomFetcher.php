<?php

namespace NPRadio\DataFetcher;

class HttpDomFetcher implements DomFetcher
{
    protected function getUrlContent(string $url)
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('invalid url given');
        }

        $content = file_get_contents($url);
        if ($content === false) {
            throw new \RuntimeException('could not fetch data from url "' . $url . '"');
        }

        return $content;
    }

    public function getXmlDom(string $url): \DOMDocument
    {
        $xml = $this->getUrlContent($url);

        $dom = new \DOMDocument();
        if ($dom->loadXML($xml) === false) {
            throw new \RuntimeException('could not parse xml data');
        }

        return $dom;
    }

    public function getHtmlDom(string $url): \DOMDocument
    {
        $html = $this->getUrlContent($url);

        $dom = new \DOMDocument();
        // sadly I haven't found a better solution than ignoring any errors that
        // might occur, because the internet is broken, right?
        if (@$dom->loadHTML($html) === false) {
            throw new \RuntimeException('could not parse html data');
        }

        return $dom;
    }
}

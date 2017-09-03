<?php

namespace NPRadio\DataFetcher;

class HttpDomFetcher implements DomFetcher
{
    protected function getUrlContent(string $url): string
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if ($url === false) {
            throw new \InvalidArgumentException('invalid url given');
        }

        try {
            $content = file_get_contents($url);
            if ($content === false) {
                throw new \RuntimeException('file_get_contents returned false');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('could not fetch data from url "' . $url . '": ' . $e->getMessage());
        }

        return $content;
    }

    public function getXmlDom(string $url): \DOMDocument
    {
        $xml = $this->getUrlContent($url);

        $dom = new \DOMDocument();
        try {
            if ($dom->loadXML($xml) === false) {
                throw new \RuntimeException('\DomDocument::loadXML returned false');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('could not parse xml data: ' . $e->getMessage());
        }

        return $dom;
    }

    public function getHtmlDom(string $url): \DOMDocument
    {
        $html = $this->getUrlContent($url);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        try {
            if ($dom->loadHTML($html) === false) {
                throw new \RuntimeException('\DomDocument::loadHTML returned false');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('couls not parse html data: ' . $e->getMessage());
        }

        return $dom;
    }
}

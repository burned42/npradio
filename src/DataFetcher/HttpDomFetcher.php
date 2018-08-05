<?php

declare(strict_types=1);

namespace App\DataFetcher;

class HttpDomFetcher implements DomFetcher
{
    /**
     * @param string $url
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getUrlContent(string $url): string
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);
        if (false === $url) {
            throw new \InvalidArgumentException('invalid url given: "'.$url.'"');
        }

        try {
            $content = file_get_contents($url);
            if (false === $content) {
                throw new \RuntimeException('file_get_contents returned false');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('could not fetch data from url "'.$url.'": '.$e->getMessage());
        }

        return $content;
    }

    /**
     * @param string $url
     *
     * @return \DOMDocument
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getXmlDom(string $url): \DOMDocument
    {
        $xml = $this->getUrlContent($url);

        $dom = new \DOMDocument();
        try {
            if (false === $dom->loadXML($xml)) {
                throw new \RuntimeException('\DomDocument::loadXML returned false');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('could not parse xml data: '.$e->getMessage());
        }

        return $dom;
    }

    /**
     * @param string $url
     *
     * @return \DOMDocument
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getHtmlDom(string $url): \DOMDocument
    {
        $html = $this->getUrlContent($url);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        try {
            if (false === $dom->loadHTML($html)) {
                throw new \RuntimeException('\DomDocument::loadHTML returned false');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('could not parse html data: '.$e->getMessage());
        }

        return $dom;
    }
}

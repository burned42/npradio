<?php

declare(strict_types=1);

namespace NPRadio\DataFetcher;

interface DomFetcher
{
    /**
     * @param string $url
     *
     * @return \DOMDocument
     */
    public function getXmlDom(string $url): \DOMDocument;

    /**
     * @param string $url
     *
     * @return \DOMDocument
     */
    public function getHtmlDom(string $url): \DOMDocument;
}

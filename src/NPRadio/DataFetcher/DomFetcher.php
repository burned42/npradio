<?php

declare(strict_types=1);

namespace NPRadio\DataFetcher;

interface DomFetcher
{
    public function getXmlDom(string $url): \DOMDocument;

    public function getHtmlDom(string $url): \DOMDocument;
}

<?php

declare(strict_types=1);

namespace App\DataFetcher;

use DOMDocument;

interface DomFetcher
{
    public function getUrlContent(string $url): string;

    public function getXmlDom(string $url): DOMDocument;

    public function getHtmlDom(string $url): DOMDocument;
}

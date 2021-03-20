<?php

declare(strict_types=1);

namespace App\DataFetcher;

use DOMDocument;

interface HttpDataFetcherInterface
{
    /**
     * @param array<string, string> $headers
     *
     * @return array<mixed>
     */
    public function getJsonData(
        string $url,
        array $headers,
        int $cacheDuration
    ): array;

    public function getUrlContent(string $url): string;

    public function getXmlDom(string $url): DOMDocument;

    public function getHtmlDom(string $url): DOMDocument;
}

<?php

declare(strict_types=1);

namespace App\DataFetcher;

use Dom\HTMLDocument;
use Dom\XMLDocument;

interface HttpDataFetcherInterface
{
    public const int DEFAULT_CACHE_DURATION_IN_SECONDS = 30;

    /**
     * @param array<string, string> $headers
     *
     * @return array<mixed>
     */
    public function getJsonData(
        string $url,
        array $headers = [],
        int $cacheDuration = self::DEFAULT_CACHE_DURATION_IN_SECONDS,
    ): array;

    public function getXmlDom(string $url): XMLDocument;

    public function getHtmlDom(string $url): HTMLDocument;
}

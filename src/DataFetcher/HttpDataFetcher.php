<?php

declare(strict_types=1);

namespace App\DataFetcher;

use Dom\HTMLDocument;
use Dom\XMLDocument;
use Override;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HttpDataFetcher implements HttpDataFetcherInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * @param array<string, mixed> $headers
     *
     * @return ($json is true ? array<mixed> : string)
     */
    private function request(
        string $url,
        array $headers = [],
        bool $json = false,
        int $cacheDurationInSeconds = self::DEFAULT_CACHE_DURATION_IN_SECONDS,
    ): array|string {
        /** @var array<mixed>|string $response */
        $response = $this->cache->get(
            $this->slugger->slug($url)->toString(),
            function (ItemInterface $item) use ($url, $headers, $json, $cacheDurationInSeconds): array|string {
                $item->expiresAfter($cacheDurationInSeconds);

                $response = $this->httpClient->request(
                    'GET',
                    $url,
                    ['headers' => $headers]
                );

                if ($json) {
                    return $response->toArray();
                }

                return $response->getContent();
            }
        );

        return $response;
    }

    #[Override]
    public function getJsonData(
        string $url,
        array $headers = [],
        int $cacheDuration = self::DEFAULT_CACHE_DURATION_IN_SECONDS,
    ): array {
        /** @var array<mixed> $response */
        $response = $this->request($url, $headers, true, $cacheDuration);

        return $response;
    }

    #[Override]
    public function getXmlDom(string $url): XMLDocument
    {
        $xml = $this->request($url);

        return XMLDocument::createFromString($xml, LIBXML_NOERROR);
    }

    #[Override]
    public function getHtmlDom(string $url): HTMLDocument
    {
        $html = $this->request($url);

        return HTMLDocument::createFromString($html, LIBXML_NOERROR);
    }
}

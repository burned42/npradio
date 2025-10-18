<?php

declare(strict_types=1);

namespace App\DataFetcher;

use Dom\HTMLDocument;
use Dom\XMLDocument;
use Exception;
use Override;
use RuntimeException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

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

                try {
                    $response = $this->httpClient->request(
                        'GET',
                        $url,
                        ['headers' => $headers]
                    );

                    if ($json) {
                        return $response->toArray();
                    }

                    return $response->getContent();
                } catch (Throwable $t) {
                    $message = 'could not fetch data from url "'.$url.'": '.$t->getMessage();

                    throw new RuntimeException($message, previous: $t);
                }
            }
        );

        return $response;
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function getJsonData(
        string $url,
        array $headers = [],
        int $cacheDuration = self::DEFAULT_CACHE_DURATION_IN_SECONDS,
    ): array {
        try {
            /** @var array<mixed> $response */
            $response = $this->request($url, $headers, true, $cacheDuration);

            return $response;
        } catch (Throwable $t) {
            $message = 'could not fetch json data from url "'.$url.'": '.$t->getMessage();

            throw new RuntimeException($message, previous: $t);
        }
    }

    #[Override]
    public function getXmlDom(string $url): XMLDocument
    {
        $xml = $this->request($url);

        try {
            return XMLDocument::createFromString($xml);
        } catch (Exception $e) {
            throw new RuntimeException('could not parse xml data: '.$e->getMessage(), previous: $e);
        }
    }

    #[Override]
    public function getHtmlDom(string $url): HTMLDocument
    {
        $html = $this->request($url);

        try {
            return HTMLDocument::createFromString($html, LIBXML_NOERROR);
        } catch (Throwable $t) {
            throw new RuntimeException('could not parse html data: '.$t->getMessage(), previous: $t);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\DataFetcher;

use DOMDocument;
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
    private const int DEFAULT_CACHE_DURATION = 30;

    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
        private SluggerInterface $slugger,
    ) {
    }

    /**
     * @param array<string, mixed> $headers
     *
     * @return array<mixed>|string
     */
    private function request(
        string $url,
        array $headers = [],
        bool $json = false,
        int $cacheDuration = self::DEFAULT_CACHE_DURATION,
    ): array|string {
        /** @var array<mixed>|string $response */
        $response = $this->cache->get(
            $this->slugger->slug($url)->toString(),
            function (ItemInterface $item) use ($url, $headers, $json, $cacheDuration): array|string {
                $item->expiresAfter($cacheDuration);

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
                    throw new RuntimeException('could not fetch json data from url "'.$url.'": '.$t->getMessage());
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
        int $cacheDuration = self::DEFAULT_CACHE_DURATION,
    ): array {
        try {
            /** @var array<mixed> $response */
            $response = $this->request($url, $headers, true, $cacheDuration);

            return $response;
        } catch (Throwable $t) {
            throw new RuntimeException('could not fetch json data from url "'.$url.'": '.$t->getMessage());
        }
    }

    #[Override]
    public function getUrlContent(string $url): string
    {
        try {
            /** @var string $response */
            $response = $this->request($url);

            return $response;
        } catch (Throwable $t) {
            throw new RuntimeException('could not fetch data from url "'.$url.'": '.$t->getMessage());
        }
    }

    #[Override]
    public function getXmlDom(string $url): DOMDocument
    {
        $xml = $this->getUrlContent($url);

        $dom = new DOMDocument();
        try {
            $domLoadXML = $dom->loadXML($xml);
            if (false === $domLoadXML) {
                throw new RuntimeException('\DomDocument::loadXML returned false');
            }
        } catch (Exception $e) {
            throw new RuntimeException('could not parse xml data: '.$e->getMessage());
        }

        return $dom;
    }

    #[Override]
    public function getHtmlDom(string $url): DOMDocument
    {
        $html = $this->getUrlContent($url);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        try {
            if (!$dom->loadHTML($html)) {
                throw new RuntimeException('\DomDocument::loadHTML returned false');
            }
        } catch (Throwable $t) {
            throw new RuntimeException('could not parse html data: '.$t->getMessage());
        }

        return $dom;
    }
}

<?php

declare(strict_types=1);

namespace App\DataFetcher;

use DOMDocument;
use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class HttpDataFetcher implements HttpDataFetcherInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getJsonData(string $url, array $headers = []): array
    {
        try {
            return $this->httpClient->request(
                'GET',
                $url,
                ['headers' => $headers]
            )->toArray();
        } catch (Throwable $t) {
            throw new RuntimeException('could not fetch json data from url "'.$url.'": '.$t->getMessage());
        }
    }

    public function getUrlContent(string $url): string
    {
        try {
            return $this->httpClient->request('GET', $url)->getContent();
        } catch (Throwable $t) {
            throw new RuntimeException('could not fetch data from url "'.$url.'": '.$t->getMessage());
        }
    }

    public function getXmlDom(string $url): DOMDocument
    {
        $xml = $this->getUrlContent($url);

        $dom = new DOMDocument();
        try {
            if (false === $dom->loadXML($xml)) {
                throw new RuntimeException('\DomDocument::loadXML returned false');
            }
        } catch (Exception $e) {
            throw new RuntimeException('could not parse xml data: '.$e->getMessage());
        }

        return $dom;
    }

    public function getHtmlDom(string $url): DOMDocument
    {
        $html = $this->getUrlContent($url);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        try {
            if (false === $dom->loadHTML($html)) {
                throw new RuntimeException('\DomDocument::loadHTML returned false');
            }
        } catch (Throwable $t) {
            throw new RuntimeException('could not parse html data: '.$t->getMessage());
        }

        return $dom;
    }
}

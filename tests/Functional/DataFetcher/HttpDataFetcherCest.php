<?php

declare(strict_types=1);

namespace Tests\Functional\DataFetcher;

use App\DataFetcher\HttpDataFetcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Tests\Support\FunctionalTester;

final class HttpDataFetcherCest
{
    public function testResponsesGetCached(FunctionalTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient([
                new MockResponse('["foobar"]'),
                new MockResponse('["meep"]'),
            ]),
            $I->grabService(CacheInterface::class),
            $I->grabService(SluggerInterface::class)
        );

        $I->assertEquals(['foobar'], $httpDataFetcher->getJsonData('https://example.com'));
        $I->assertEquals(['foobar'], $httpDataFetcher->getJsonData('https://example.com'));
    }

    public function testJsonResponsesGetCached(FunctionalTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient([
                new MockResponse('["foobar"]'),
                new MockResponse('["meep"]'),
            ]),
            $I->grabService(CacheInterface::class),
            $I->grabService(SluggerInterface::class)
        );

        $I->assertEquals(['foobar'], $httpDataFetcher->getJsonData('https://example.com'));
        $I->assertEquals(['foobar'], $httpDataFetcher->getJsonData('https://example.com'));
    }
}

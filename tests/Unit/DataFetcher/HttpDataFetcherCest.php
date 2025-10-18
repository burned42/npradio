<?php

declare(strict_types=1);

namespace Tests\Unit\DataFetcher;

use App\DataFetcher\HttpDataFetcher;
use App\DataFetcher\HttpDataFetcherInterface;
use Codeception\Stub;
use Dom\HTMLDocument;
use Dom\XMLDocument;
use DOMException;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tests\Support\UnitTester;

final class HttpDataFetcherCest
{
    private CacheInterface $cache;
    private SluggerInterface $slugger;

    public function _before(): void
    {
        $this->cache = Stub::makeEmpty(
            CacheInterface::class,
            ['get' => fn ($key, $fn) => $fn(Stub::makeEmpty(ItemInterface::class))]
        );
        $this->slugger = Stub::makeEmpty(
            SluggerInterface::class,
            ['slug' => fn ($string) => Stub::make(
                UnicodeString::class,
                ['toString' => $string]
            )]
        );
    }

    public function testCanInstantiate(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient(),
            $this->cache,
            $this->slugger
        );

        $I->assertInstanceOf(HttpDataFetcherInterface::class, $httpDataFetcher);
        $I->assertInstanceOf(HttpDataFetcher::class, $httpDataFetcher);
    }

    public function testGetJsonData(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient(
                new MockResponse(
                    file_get_contents(
                        'file://'.__DIR__.'/../TestSamples/JsonExample.json'
                    )
                )
            ),
            $this->cache,
            $this->slugger
        );

        $expected = ['foo' => ['bar', 'baz']];
        $data = $httpDataFetcher->getJsonData('https://api.rautemusik.fm/streams_onair/');

        $I->assertEquals($expected, $data);
    }

    public function testGetJsonDataExceptionOnError(UnitTester $I): void
    {
        $callback = fn () => throw new RuntimeException('test');
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient($callback),
            $this->cache,
            $this->slugger
        );

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $httpDataFetcher->getJsonData('https://api.rautemusik.fm/streams_onair/')
        );
    }

    public function testGetXmlDom(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient(
                new MockResponse(
                    file_get_contents(
                        'file://'.__DIR__.'/../TestSamples/TechnoBaseSample.xml'
                    )
                )
            ),
            $this->cache,
            $this->slugger
        );

        $I->assertInstanceOf(
            XMLDocument::class,
            $httpDataFetcher->getXmlDom('https://tray.technobase.fm/radio.xml')
        );
    }

    public function testExceptionOnBrokenXml(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient(
                new MockResponse(
                    file_get_contents(
                        'file://'.__DIR__.'/../TestSamples/TechnoBaseSampleBroken.xml'
                    )
                )
            ),
            $this->cache,
            $this->slugger
        );

        $I->expectThrowable(
            DOMException::class,
            fn () => $httpDataFetcher->getXmlDom('https://tray.technobase.fm/radio.xml')
        );
    }

    public function testGetHtmlDom(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient(
                new MockResponse(
                    file_get_contents(
                        'file://'.__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html'
                    )
                )
            ),
            $this->cache,
            $this->slugger
        );

        $I->assertInstanceOf(
            HTMLDocument::class,
            $httpDataFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }

    public function testNoExceptionOnBrokenHtml(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient(
                new MockResponse(
                    file_get_contents(
                        'file://'.__DIR__.'/../TestSamples/Empty.html'
                    )
                )
            ),
            $this->cache,
            $this->slugger
        );

        $I->assertInstanceOf(
            HTMLDocument::class,
            $httpDataFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }
}

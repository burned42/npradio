<?php

declare(strict_types=1);

namespace App\Tests\unit\DataFetcher;

use App\DataFetcher\HttpDataFetcher;
use App\DataFetcher\HttpDataFetcherInterface;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use DOMDocument;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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

    public function testGetUrlContent(UnitTester $I): void
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

        $data = $httpDataFetcher->getUrlContent('https://api.rautemusik.fm/streams_onair/');

        $expected = <<<EXAMPLE
            {
              "foo": [
                "bar",
                "baz"
              ]
            }
            EXAMPLE;
        $I->assertEquals($expected, $data);
    }

    public function testGetUrlContentExceptionOnError(UnitTester $I): void
    {
        $callback = fn () => throw new RuntimeException('test');
        $httpDataFetcher = new HttpDataFetcher(
            new MockHttpClient($callback),
            $this->cache,
            $this->slugger
        );

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $httpDataFetcher->getUrlContent('https://api.rautemusik.fm/streams_onair/')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
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
            DOMDocument::class,
            $httpDataFetcher->getXmlDom('http://tray.technobase.fm/radio.xml')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
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
            RuntimeException::class,
            fn () => $httpDataFetcher->getXmlDom('http://tray.technobase.fm/radio.xml')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
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
            DOMDocument::class,
            $httpDataFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testExceptionOnBrokenHtml(UnitTester $I): void
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

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $httpDataFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }
}

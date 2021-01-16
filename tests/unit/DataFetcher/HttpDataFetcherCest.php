<?php

declare(strict_types=1);

namespace App\Tests\unit\DataFetcher;

use App\DataFetcher\HttpDataFetcher;
use App\DataFetcher\HttpDataFetcherInterface;
use App\Tests\UnitTester;
use DOMDocument;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HttpDataFetcherCest
{
    public function testCanInstantiate(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient());

        $I->assertInstanceOf(HttpDataFetcherInterface::class, $httpDataFetcher);
        $I->assertInstanceOf(HttpDataFetcher::class, $httpDataFetcher);
    }

    public function testGetJsonData(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/JsonExample.json'
                )
            )
        ));

        $expected = ['foo' => ['bar', 'baz']];
        $data = $httpDataFetcher->getJsonData('https://api.rautemusik.fm/streams_onair/');

        $I->assertEquals($expected, $data);
    }

    public function testGetJsonDataExceptionOnError(UnitTester $I): void
    {
        $callback = fn () => throw new RuntimeException('test');
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient($callback));

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $httpDataFetcher->getJsonData('https://api.rautemusik.fm/streams_onair/')
        );
    }

    public function testGetUrlContent(UnitTester $I): void
    {
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/JsonExample.json'
                )
            )
        ));

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
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient($callback));

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
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/TechnoBaseSample.xml'
                )
            )
        ));

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
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/TechnoBaseSampleBroken.xml'
                )
            )
        ));

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
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html'
                )
            )
        ));

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
        $httpDataFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/Empty.html'
                )
            )
        ));

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $httpDataFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }
}

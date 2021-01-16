<?php

declare(strict_types=1);

namespace App\Tests\unit\DataFetcher;

use App\DataFetcher\DomFetcherInterface;
use App\DataFetcher\HttpDataFetcher;
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
        $domFetcher = new HttpDataFetcher(new MockHttpClient());

        $I->assertInstanceOf(DomFetcherInterface::class, $domFetcher);
        $I->assertInstanceOf(HttpDataFetcher::class, $domFetcher);
    }

    public function testGetJsonData(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/JsonExample.json'
                )
            )
        ));

        $expected = ['foo' => ['bar', 'baz']];
        $data = $domFetcher->getJsonData('https://api.rautemusik.fm/streams_onair/');

        $I->assertEquals($expected, $data);
    }

    public function testGetJsonDataExceptionOnError(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(
            new MockHttpClient(
                fn () => throw new RuntimeException('test')
            )
        );

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $domFetcher->getJsonData('https://api.rautemusik.fm/streams_onair/')
        );
    }

    public function testGetUrlContent(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/JsonExample.json'
                )
            )
        ));

        $data = $domFetcher->getUrlContent('https://api.rautemusik.fm/streams_onair/');

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
        $domFetcher = new HttpDataFetcher(
            new MockHttpClient(
                fn () => throw new RuntimeException('test')
            )
        );

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $domFetcher->getUrlContent('https://api.rautemusik.fm/streams_onair/')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testGetXmlDom(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/TechnoBaseSample.xml'
                )
            )
        ));

        $I->assertInstanceOf(
            DOMDocument::class,
            $domFetcher->getXmlDom('http://tray.technobase.fm/radio.xml')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testExceptionOnBrokenXml(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/TechnoBaseSampleBroken.xml'
                )
            )
        ));

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $domFetcher->getXmlDom('http://tray.technobase.fm/radio.xml')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testGetHtmlDom(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html'
                )
            )
        ));

        $I->assertInstanceOf(
            DOMDocument::class,
            $domFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testExceptionOnBrokenHtml(UnitTester $I): void
    {
        $domFetcher = new HttpDataFetcher(new MockHttpClient(
            new MockResponse(
                file_get_contents(
                    'file://'.__DIR__.'/../TestSamples/Empty.html'
                )
            )
        ));

        $I->expectThrowable(
            RuntimeException::class,
            fn () => $domFetcher->getHtmlDom('https://www.metal-only.de/sendeplan.html')
        );
    }
}

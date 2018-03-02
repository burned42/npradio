<?php

declare(strict_types=1);

namespace NPRadio\DataFetcher;

use UnitTester;

class HttpDomFetcherCest
{
    /** @var HttpDomFetcher */
    private $domFetcher;

    public function _before(UnitTester $I)
    {
        $this->domFetcher = new HttpDomFetcher();
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testExceptionOnInvalidUrl(UnitTester $I)
    {
        $I->expectException(
            new \InvalidArgumentException('invalid url given'),
            function () {
                $this->domFetcher->getHtmlDom('invalid_url');
            }
        );

        $I->expectException(
            new \InvalidArgumentException('invalid url given'),
            function () {
                $this->domFetcher->getXmlDom('invalid_url');
            }
        );
    }

    public function testExceptionOnNonExistingUrl(UnitTester $I)
    {
        $url = 'http://127.0.0.1/this/foo/does/hopefully/never.exist';

        $I->expectException(
            \RuntimeException::class,
            function () use ($url) {
                $this->domFetcher->getHtmlDom($url);
            }
        );

        $I->expectException(
            \RuntimeException::class,
            function () use ($url) {
                $this->domFetcher->getXmlDom($url);
            }
        );
    }

    public function testGetXmlDom(UnitTester $I)
    {
        $xmlDom = $this->domFetcher->getXmlDom('file://'.__DIR__.'/../TestSamples/TechnoBaseSample.xml');

        $I->assertInstanceOf(\DOMDocument::class, $xmlDom);
    }

    public function testExceptionOnBrokenXml(UnitTester $I)
    {
        $I->expectException(
            \RuntimeException::class,
            function () {
                $this->domFetcher->getXmlDom('file://'.__DIR__.'/../TestSamples/TechnoBaseSampleBroken.xml');
            }
        );
    }

    public function testGetHtmlDom(UnitTester $I)
    {
        $htmlDom = $this->domFetcher->getHtmlDom('file://'.__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html');

        $I->assertInstanceOf(\DOMDocument::class, $htmlDom);
    }

    public function testExceptionOnBrokenHtml(UnitTester $I)
    {
        $I->expectException(
            \RuntimeException::class,
            function () {
                $this->domFetcher->getHtmlDom('file://'.__DIR__.'/../TestSamples/Empty.html');
            }
        );
    }
}

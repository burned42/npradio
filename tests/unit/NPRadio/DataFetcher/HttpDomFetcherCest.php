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

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
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

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
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

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testGetXmlDom(UnitTester $I)
    {
        $xmlDom = $this->domFetcher->getXmlDom('file://'.__DIR__.'/../TestSamples/TechnoBaseSample.xml');

        $I->assertInstanceOf(\DOMDocument::class, $xmlDom);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testExceptionOnBrokenXml(UnitTester $I)
    {
        $I->expectException(
            \RuntimeException::class,
            function () {
                $this->domFetcher->getXmlDom('file://'.__DIR__.'/../TestSamples/TechnoBaseSampleBroken.xml');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testGetHtmlDom(UnitTester $I)
    {
        $htmlDom = $this->domFetcher->getHtmlDom('file://'.__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html');

        $I->assertInstanceOf(\DOMDocument::class, $htmlDom);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
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

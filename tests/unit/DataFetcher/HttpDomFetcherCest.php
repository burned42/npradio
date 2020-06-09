<?php

declare(strict_types=1);

namespace App\Tests\unit\DataFetcher;

use App\DataFetcher\HttpDomFetcher;
use App\Tests\UnitTester;
use InvalidArgumentException;
use RuntimeException;

class HttpDomFetcherCest
{
    private HttpDomFetcher $domFetcher;

    public function _before(): void
    {
        $this->domFetcher = new HttpDomFetcher();
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testExceptionOnInvalidUrl(UnitTester $I): void
    {
        $I->expectThrowable(
            new InvalidArgumentException('invalid url given: ""'),
            function () {
                $this->domFetcher->getHtmlDom('invalid_url');
            }
        );

        $I->expectThrowable(
            new InvalidArgumentException('invalid url given: ""'),
            function () {
                $this->domFetcher->getXmlDom('invalid_url');
            }
        );
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testExceptionOnNonExistingUrl(UnitTester $I): void
    {
        $url = 'http://127.0.0.1/this/foo/does/hopefully/never.exist';

        $I->expectThrowable(
            RuntimeException::class,
            function () use ($url) {
                $this->domFetcher->getHtmlDom($url);
            }
        );

        $I->expectThrowable(
            RuntimeException::class,
            function () use ($url) {
                $this->domFetcher->getXmlDom($url);
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testGetXmlDom(UnitTester $I): void
    {
        $this->domFetcher->getXmlDom('file://'.__DIR__.'/../TestSamples/TechnoBaseSample.xml');

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testExceptionOnBrokenXml(UnitTester $I): void
    {
        $I->expectThrowable(
            RuntimeException::class,
            function () {
                $this->domFetcher->getXmlDom('file://'.__DIR__.'/../TestSamples/TechnoBaseSampleBroken.xml');
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testGetHtmlDom(UnitTester $I): void
    {
        $this->domFetcher->getHtmlDom('file://'.__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html');

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testExceptionOnBrokenHtml(UnitTester $I): void
    {
        $I->expectThrowable(
            RuntimeException::class,
            function () {
                $this->domFetcher->getHtmlDom('file://'.__DIR__.'/../TestSamples/Empty.html');
            }
        );
    }
}

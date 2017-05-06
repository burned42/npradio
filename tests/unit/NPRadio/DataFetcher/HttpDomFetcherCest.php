<?php
namespace NPRadio\DataFetcher;
use \UnitTester;

class HttpDomFetcherCest
{
    /** @var  HttpDomFetcher */
    private $domFetcher;

    public function _before(UnitTester $I)
    {
        $this->domFetcher = new HttpDomFetcher();
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function exceptionOnInvalidUrl(UnitTester $I)
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
}

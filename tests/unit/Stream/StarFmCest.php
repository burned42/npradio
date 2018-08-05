<?php

declare(strict_types=1);

namespace App\Stream;

use Codeception\Util\Stub;
use App\DataFetcher\HttpDomFetcher;
use UnitTester;

class StarFmCest
{
    private $domFetcher;

    /**
     * @throws \Exception
     */
    public function _before()
    {
        $this->domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/StarFmSample.json');
            @$dom->loadHTML($html);

            return $dom;
        }]);
    }

    public function canInstantiate(UnitTester $I)
    {
        $starFm = new StarFm($this->domFetcher, StarFm::getAvailableStreams()[0]);

        $I->assertInstanceOf(StarFm::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotEmpty(StarFm::getRadioName());
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(StarFm::getAvailableStreams());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I)
    {
        foreach (StarFm::getAvailableStreams() as $availableStream) {
            new StarFm($this->domFetcher, $availableStream);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testDomFetcherException(UnitTester $I)
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => function () {
            throw new \RuntimeException('test');
        }]);

        $I->expectException(
            new \RuntimeException('could not get url content: test'),
            function () use ($domFetcher) {
                new StarFm($domFetcher, StarFm::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I)
    {
        $starFm = new StarFm($this->domFetcher, StarFm::getAvailableStreams()[0]);
        $info = $starFm->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

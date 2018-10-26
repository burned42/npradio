<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stream;

use App\Stream\RauteMusik;
use Codeception\Util\Stub;
use App\DataFetcher\HttpDomFetcher;
use UnitTester;

class RauteMusikCest
{
    /**
     * @return HttpDomFetcher|object
     *
     * @throws \Exception
     */
    private function getDomFetcher()
    {
        $trackInfoDom = new \DOMDocument();
        $html = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubTrackInfoSample.html');
        libxml_use_internal_errors(true);
        $trackInfoDom->loadHTML($html);

        $showInfoDom = new \DOMDocument();
        $html = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubShowInfoSample.html');
        libxml_use_internal_errors(true);
        $showInfoDom->loadHTML($html);

        /* @var HttpDomFetcher $domFetcher */
        return Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => Stub::consecutive(
            $trackInfoDom,
            $showInfoDom
        )]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function canInstantiate(UnitTester $I)
    {
        $rm = new RauteMusik($this->getDomFetcher(), RauteMusik::getAvailableStreams()[0]);

        $I->assertInstanceOf(RauteMusik::class, $rm);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotEmpty(RauteMusik::getRadioName());
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(RauteMusik::getAvailableStreams());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testUpdateInfo(UnitTester $I)
    {
        foreach (RauteMusik::getAvailableStreams() as $availableStream) {
            new RauteMusik($this->getDomFetcher(), $availableStream);
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
    public function testDomFetcherExceptionOnTrackInfo(UnitTester $I)
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => function () {
            throw new \RuntimeException('test');
        }]);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($domFetcher) {
                new RauteMusik($domFetcher, RauteMusik::getAvailableStreams()[0]);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testDomFetcherExceptionOnShowInfo(UnitTester $I)
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => function () {
            static $first = true;
            if ($first) {
                $first = false;

                $trackInfoDom = new \DOMDocument();
                $xml = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubTrackInfoSample.html');
                @$trackInfoDom->loadXML($xml);

                return $trackInfoDom;
            }

            // throw exception on second call to test fetchShowInfo()
            throw new \RuntimeException('test');
        }]);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($domFetcher) {
                new RauteMusik($domFetcher, RauteMusik::getAvailableStreams()[0]);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProtectedMethods(UnitTester $I)
    {
        $rm = new RauteMusik($this->getDomFetcher(), RauteMusik::getAvailableStreams()[0]);
        $info = $rm->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

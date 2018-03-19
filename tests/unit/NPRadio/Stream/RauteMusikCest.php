<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use Codeception\Util\Stub;
use NPRadio\DataFetcher\DomFetcher;
use NPRadio\DataFetcher\HttpDomFetcher;
use UnitTester;

class RauteMusikCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function canInstantiate(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class);

        $rm = new RauteMusik($domFetcher);

        $I->assertInstanceOf(RauteMusik::class, $rm);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotNull(RauteMusik::RADIO_NAME);
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(RauteMusik::AVAILABLE_STREAMS);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetInfo(UnitTester $I)
    {
        $trackInfoDom = new \DOMDocument();
        $xml = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubTrackInfoSample.html');
        @$trackInfoDom->loadXML($xml);

        $showInfoDom = new \DOMDocument();
        $xml = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubShowInfoSample.html');
        @$showInfoDom->loadXML($xml);

        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => Stub::consecutive(
            $trackInfoDom,
            $showInfoDom
        )]);

        $rm = new RauteMusik($domFetcher);

        $info = $rm->getInfo(RauteMusik::CLUB);

        $I->assertInstanceOf(StreamInfo::class, $info);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetInfoForNonExistingStream(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class);

        $rm = new RauteMusik($domFetcher);

        $streamName = 'foobar test';
        $I->expectException(
            new \InvalidArgumentException('no radio info object created for stream: '.$streamName),
            function () use ($rm, $streamName) {
                $rm->getInfo($streamName);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testWithLiveData(UnitTester $I)
    {
        $tb = new RauteMusik(new HttpDomFetcher());

        foreach (RauteMusik::AVAILABLE_STREAMS as $streamName) {
            $info = $tb->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testDomFetcherExceptionOnTrackInfo(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            throw new \RuntimeException('test');
        }]);
        $mo = new RauteMusik($domFetcher);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($mo) {
                $mo->getInfo(RauteMusik::AVAILABLE_STREAMS[0]);
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
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
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
        $mo = new RauteMusik($domFetcher);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($mo) {
                $mo->getInfo(RauteMusik::AVAILABLE_STREAMS[0]);
            }
        );
    }
}

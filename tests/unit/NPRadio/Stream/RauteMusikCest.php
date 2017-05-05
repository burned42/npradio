<?php

namespace NPRadio\Stream;

use Codeception\Util\Stub;
use NPRadio\DataFetcher\DomFetcher;
use \UnitTester;

class RauteMusikCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
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

    public function testGetInfo(UnitTester $I)
    {
        $trackInfoDom = new \DOMDocument();
        $xml = file_get_contents(__DIR__ . '/RauteMusikClubTrackInfoSample.html');
        @$trackInfoDom->loadXML($xml);

        $showInfoDom = new \DOMDocument();
        $xml = file_get_contents(__DIR__ . '/RauteMusikClubShowInfoSample.html');
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

    public function testGetInfoForNonExistingStream(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class);

        $rm = new RauteMusik($domFetcher);

        $streamName = 'foobar test';
        $I->expectException(
            new \InvalidArgumentException('no radio info object created for stream: ' . $streamName),
            function () use ($rm, $streamName) {
                $rm->getInfo($streamName);
            }
        );
    }
}

<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use Codeception\Util\Stub;
use NPRadio\DataFetcher\DomFetcher;
use UnitTester;

class StarFmCest
{
    private $domFetcher;

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function _before(UnitTester $I)
    {
        $this->domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/Empty.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function canInstantiate(UnitTester $I)
    {
        $starFm = new StarFm($this->domFetcher);

        $I->assertInstanceOf(StarFm::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotNull(StarFm::RADIO_NAME);
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(StarFm::AVAILABLE_STREAMS);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testGetInfo(UnitTester $I)
    {
        $starFm = new StarFm($this->domFetcher);

        foreach (StarFm::AVAILABLE_STREAMS as $streamName) {
            $info = $starFm->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testGetInfoForNonExistingStream(UnitTester $I)
    {
        $starFm = new StarFm($this->domFetcher);

        $streamName = 'foobar test';
        $I->expectException(
            new \InvalidArgumentException('no radio info object created for stream: '.$streamName),
            function () use ($starFm, $streamName) {
                $starFm->getInfo($streamName);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testDomFetcherException(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getUrlContent' => function () {
            throw new \RuntimeException('test');
        }]);
        $starFm = new StarFm($domFetcher);

        $I->expectException(
            new \RuntimeException('could not get url content: test'),
            function () use ($starFm) {
                $starFm->getInfo(StarFm::AVAILABLE_STREAMS[0]);
            }
        );
    }
}

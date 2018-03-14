<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use Codeception\Util\Stub;
use NPRadio\DataFetcher\DomFetcher;
use NPRadio\DataFetcher\HttpDomFetcher;
use UnitTester;

class MetalOnlyCest
{
    private $domFetcher;
    private $domFetcherNotOnAir;

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function _before(UnitTester $I)
    {
        $this->domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySample.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);

        $this->domFetcherNotOnAir = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html');
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
        $mo = new MetalOnly($this->domFetcher);

        $I->assertInstanceOf(MetalOnly::class, $mo);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotNull(MetalOnly::RADIO_NAME);
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(MetalOnly::AVAILABLE_STREAMS);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testGetInfo(UnitTester $I)
    {
        $mo = new MetalOnly($this->domFetcher);

        foreach (MetalOnly::AVAILABLE_STREAMS as $streamName) {
            $info = $mo->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testGetInfoNotOnAir(UnitTester $I)
    {
        $mo = new MetalOnly($this->domFetcherNotOnAir);

        foreach (MetalOnly::AVAILABLE_STREAMS as $streamName) {
            $info = $mo->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);

            $I->assertNull($info->getModerator());
            $I->assertNull($info->getShow());
            $I->assertNull($info->getGenre());
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
        $mo = new MetalOnly($this->domFetcher);

        $streamName = 'foobar test';
        $I->expectException(
            new \InvalidArgumentException('no radio info object created for stream: '.$streamName),
            function () use ($mo, $streamName) {
                $mo->getInfo($streamName);
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
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            throw new \RuntimeException('test');
        }]);
        $mo = new MetalOnly($domFetcher);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($mo) {
                $mo->getInfo(MetalOnly::AVAILABLE_STREAMS[0]);
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
        $tb = new MetalOnly(new HttpDomFetcher());

        foreach (MetalOnly::AVAILABLE_STREAMS as $streamName) {
            $info = $tb->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }
}

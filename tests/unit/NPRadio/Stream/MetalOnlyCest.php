<?php

namespace NPRadio\Stream;

use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Stub;
use NPRadio\DataFetcher\DomFetcher;
use \UnitTester;

class MetalOnlyCest
{
    private $domFetcher;

    public function _before(UnitTester $I)
    {
        $this->domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__ . '/MetalOnlySample.html');
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

    public function testGetInfo(UnitTester $I)
    {
        $mo = new MetalOnly($this->domFetcher);

        foreach (MetalOnly::AVAILABLE_STREAMS as $streamName) {
            $info = $mo->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }

    public function testGetInfoForNonExistingStream(UnitTester $I)
    {
        $mo = new MetalOnly($this->domFetcher);

        $streamName = 'foobar test';
        $I->expectException(
            new \InvalidArgumentException('invalid stream name given: ' . $streamName),
            function () use ($mo, $streamName) {
                $mo->getInfo($streamName);
            }
        );
    }

    public function testDomFetcherException(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getHtmlDom' => function () {
            throw new TestRuntimeException('test');
        }]);
        $mo = new MetalOnly($domFetcher);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($mo) {
                $mo->getInfo(MetalOnly::AVAILABLE_STREAMS[0]);
            }
        );
    }
}

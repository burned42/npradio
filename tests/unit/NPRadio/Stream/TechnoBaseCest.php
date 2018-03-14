<?php

declare(strict_types=1);

namespace NPRadio\Stream;

use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Stub;
use NPRadio\DataFetcher\DomFetcher;
use NPRadio\DataFetcher\HttpDomFetcher;
use UnitTester;

class TechnoBaseCest
{
    private $domFetcher;

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function _before(UnitTester $I)
    {
        $this->domFetcher = Stub::makeEmpty(DomFetcher::class, ['getXmlDom' => function () {
            $dom = new \DOMDocument();
            $xml = file_get_contents(__DIR__.'/../TestSamples/TechnoBaseSample.xml');
            @$dom->loadXML($xml);

            return $dom;
        }]);
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function canInstantiate(UnitTester $I)
    {
        $tb = new TechnoBase($this->domFetcher);

        $I->assertInstanceOf(TechnoBase::class, $tb);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotNull(TechnoBase::RADIO_NAME);
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(TechnoBase::AVAILABLE_STREAMS);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testGetInfo(UnitTester $I)
    {
        $tb = new TechnoBase($this->domFetcher);

        foreach (TechnoBase::AVAILABLE_STREAMS as $streamName) {
            $info = $tb->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testGetInfoForNonExistingStream(UnitTester $I)
    {
        $tb = new TechnoBase($this->domFetcher);

        $streamName = 'foobar test';
        $I->expectException(
            new \InvalidArgumentException('no radio info object created for stream: '.$streamName),
            function () use ($tb, $streamName) {
                $tb->getInfo($streamName);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testDomFetcherError(UnitTester $I)
    {
        /** @var DomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcher::class, ['getXmlDom' => function () {
            throw new TestRuntimeException('test');
        }]);
        $tb = new TechnoBase($domFetcher);

        $I->expectException(
            new \RuntimeException('could not get xml dom: test'),
            function () use ($tb) {
                $tb->getInfo(TechnoBase::AVAILABLE_STREAMS[0]);
            }
        );
    }

    /**
     * @param UnitTester $I
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testWithLiveData(UnitTester $I)
    {
        $tb = new TechnoBase(new HttpDomFetcher());

        foreach (TechnoBase::AVAILABLE_STREAMS as $streamName) {
            $info = $tb->getInfo($streamName);

            $I->assertInstanceOf(StreamInfo::class, $info);
        }
    }
}

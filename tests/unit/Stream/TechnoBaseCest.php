<?php

declare(strict_types=1);

namespace App\Stream;

use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Stub;
use App\DataFetcher\HttpDomFetcher;
use UnitTester;

class TechnoBaseCest
{
    private $domFetcher;

    /**
     * @throws \Exception
     */
    public function _before()
    {
        $this->domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getXmlDom' => function () {
            $dom = new \DOMDocument();
            $xml = file_get_contents(__DIR__.'/../TestSamples/TechnoBaseSample.xml');
            @$dom->loadXML($xml);

            return $dom;
        }]);
    }

    public function canInstantiate(UnitTester $I)
    {
        $tb = new TechnoBase($this->domFetcher, TechnoBase::getAvailableStreams()[0]);

        $I->assertInstanceOf(TechnoBase::class, $tb);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotNull(TechnoBase::getRadioName());
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(TechnoBase::getAvailableStreams());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I)
    {
        foreach (TechnoBase::getAvailableStreams() as $availableStream) {
            new TechnoBase($this->domFetcher, $availableStream);
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
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getXmlDom' => function () {
            throw new TestRuntimeException('test');
        }]);

        $I->expectException(
            new \RuntimeException('could not get xml dom: test'),
            function () use ($domFetcher) {
                new TechnoBase($domFetcher, TechnoBase::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I)
    {
        $tb = new TechnoBase($this->domFetcher, TechnoBase::getAvailableStreams()[0]);
        $info = $tb->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

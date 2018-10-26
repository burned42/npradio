<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stream;

use App\Stream\MetalOnly;
use Codeception\Util\Stub;
use App\DataFetcher\HttpDomFetcher;
use UnitTester;

class MetalOnlyCest
{
    private $domFetcher;
    private $domFetcherNotOnAir;

    /**
     * @throws \Exception
     */
    public function _before()
    {
        $this->domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySample.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);

        $this->domFetcherNotOnAir = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => function () {
            $dom = new \DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);
    }

    public function canInstantiate(UnitTester $I)
    {
        $mo = new MetalOnly($this->domFetcher, MetalOnly::getAvailableStreams()[0]);

        $I->assertInstanceOf(MetalOnly::class, $mo);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotEmpty(MetalOnly::getRadioName());
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(MetalOnly::getAvailableStreams());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I)
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            new MetalOnly($this->domFetcher, $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function testGetInfoNotOnAir(UnitTester $I)
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            $mo = new MetalOnly($this->domFetcherNotOnAir, $streamName);

            $I->assertNull($mo->getModerator());
            $I->assertNull($mo->getShow());
            $I->assertNull($mo->getGenre());
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testDomFetcherException(UnitTester $I)
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => function () {
            throw new \RuntimeException('test');
        }]);

        $I->expectException(
            new \RuntimeException('could not get html dom: test'),
            function () use ($domFetcher) {
                new MetalOnly($domFetcher, MetalOnly::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I)
    {
        $mo = new MetalOnly($this->domFetcher, MetalOnly::getAvailableStreams()[0]);
        $info = $mo->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

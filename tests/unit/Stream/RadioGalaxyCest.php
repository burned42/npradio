<?php

declare(strict_types=1);

namespace App\Stream;

use Codeception\Util\Stub;
use App\DataFetcher\HttpDomFetcher;
use UnitTester;

class RadioGalaxyCest
{
    private $domFetcher;

    /**
     * @throws \Exception
     */
    public function _before()
    {
        $this->domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => function () {
            return file_get_contents(__DIR__.'/../TestSamples/RadioGalaxySample.json');
        }]);
    }

    public function canInstantiate(UnitTester $I)
    {
        $starFm = new RadioGalaxy($this->domFetcher, RadioGalaxy::getAvailableStreams()[0]);

        $I->assertInstanceOf(RadioGalaxy::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I)
    {
        $I->assertNotEmpty(RadioGalaxy::getRadioName());
    }

    public function testStreamsSet(UnitTester $I)
    {
        $I->assertNotEmpty(RadioGalaxy::getAvailableStreams());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I)
    {
        foreach (RadioGalaxy::getAvailableStreams() as $availableStream) {
            new RadioGalaxy($this->domFetcher, $availableStream);
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
                new RadioGalaxy($domFetcher, RadioGalaxy::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I)
    {
        $starFm = new RadioGalaxy($this->domFetcher, RadioGalaxy::getAvailableStreams()[0]);
        $info = $starFm->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

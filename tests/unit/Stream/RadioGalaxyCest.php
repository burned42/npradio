<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\RadioGalaxy;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class RadioGalaxyCest
{
    private HttpDomFetcher $domFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../TestSamples/RadioGalaxySample.json');
        }]);
        $this->domFetcher = $domFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $starFm = new RadioGalaxy($this->domFetcher, RadioGalaxy::getAvailableStreams()[0]);

        $I->assertInstanceOf(RadioGalaxy::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RadioGalaxy::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RadioGalaxy::getAvailableStreams());
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I): void
    {
        foreach (RadioGalaxy::getAvailableStreams() as $availableStream) {
            new RadioGalaxy($this->domFetcher, $availableStream);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @throws Exception
     */
    public function testDomFetcherException(UnitTester $I): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => static function () {
            throw new RuntimeException('test');
        }]);

        $I->expectThrowable(
            new RuntimeException('could not get url content: test'),
            static function () use ($domFetcher) {
                new RadioGalaxy($domFetcher, RadioGalaxy::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I): void
    {
        $starFm = new RadioGalaxy($this->domFetcher, RadioGalaxy::getAvailableStreams()[0]);
        $info = $starFm->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertIsString($info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertIsString($info['stream_url']);
    }
}

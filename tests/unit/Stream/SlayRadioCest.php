<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\SlayRadio;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class SlayRadioCest
{
    private HttpDomFetcher $domFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../TestSamples/SlayRadioSample.json');
        }]);
        $this->domFetcher = $domFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $SlayRadio = new SlayRadio($this->domFetcher, SlayRadio::getAvailableStreams()[0]);

        $I->assertInstanceOf(SlayRadio::class, $SlayRadio);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(SlayRadio::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty(SlayRadio::getAvailableStreams());
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I): void
    {
        foreach (SlayRadio::getAvailableStreams() as $availableStream) {
            new SlayRadio($this->domFetcher, $availableStream);
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
                new SlayRadio($domFetcher, SlayRadio::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I): void
    {
        $slayRadio = new SlayRadio($this->domFetcher, SlayRadio::getAvailableStreams()[0]);
        $info = $slayRadio->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertIsString($info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertIsString($info['stream_url']);
    }
}

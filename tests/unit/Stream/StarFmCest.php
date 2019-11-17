<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\StarFm;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use UnitTester;

class StarFmCest
{
    private $domFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        $this->domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../TestSamples/StarFmSample.json');
        }]);
    }

    public function canInstantiate(UnitTester $I): void
    {
        $starFm = new StarFm($this->domFetcher, StarFm::getAvailableStreams()[0]);

        $I->assertInstanceOf(StarFm::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(StarFm::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty(StarFm::getAvailableStreams());
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I): void
    {
        foreach (StarFm::getAvailableStreams() as $availableStream) {
            new StarFm($this->domFetcher, $availableStream);
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
                new StarFm($domFetcher, StarFm::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I): void
    {
        $starFm = new StarFm($this->domFetcher, StarFm::getAvailableStreams()[0]);
        $info = $starFm->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

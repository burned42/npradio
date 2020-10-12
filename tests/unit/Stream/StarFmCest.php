<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\StarFm;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class StarFmCest
{
    private HttpDomFetcher $domFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../TestSamples/StarFmSample.json');
        }]);
        $this->domFetcher = $domFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $starFm = new StarFm($this->domFetcher);

        $I->assertInstanceOf(StarFm::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(StarFm::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new StarFm($this->domFetcher))->getAvailableStreams());
    }

    public function testGetStreamInfo(UnitTester $I): void
    {
        $radio = new StarFm($this->domFetcher);
        foreach ($radio->getAvailableStreams() as $streamName) {
            $info = $radio->getStreamInfo($streamName);

            $I->assertIsString($info->radioName);
            $I->assertIsString($info->streamName);
            $I->assertIsString($info->homepageUrl);
            $I->assertIsString($info->streamUrl);

            $I->assertIsString($info->artist);
            $I->assertIsString($info->track);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class);
        $s = new StarFm($domFetcher);

        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given'),
            static fn () => $s->getStreamInfo('foobar'),
        );
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
        $s = new StarFm($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get url content: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\StarFm;
use Codeception\Stub;
use InvalidArgumentException;
use RuntimeException;
use Tests\Support\UnitTester;

final class StarFmCest
{
    private HttpDataFetcherInterface $httpDataFetcher;

    public function _before(): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, [
            'getJsonData' => static fn () => json_decode(
                file_get_contents(__DIR__.'/../../TestSamples/StarFmSample.json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
        ]);
        $this->httpDataFetcher = $httpDataFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $starFm = new StarFm($this->httpDataFetcher);

        $I->assertInstanceOf(StarFm::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(StarFm::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new StarFm($this->httpDataFetcher))->getAvailableStreams());
    }

    public function testGetStreamInfo(UnitTester $I): void
    {
        $radio = new StarFm($this->httpDataFetcher);
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

    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);
        $s = new StarFm($httpDataFetcher);

        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given: foobar'),
            static fn () => $s->getStreamInfo('foobar'),
        );
    }

    public function testHttpDataException(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getJsonData' => static function () {
            throw new RuntimeException('test');
        }]);
        $s = new StarFm($httpDataFetcher);

        $streamName = $s->getAvailableStreams()[0];

        $streamInfo = $s->getStreamInfo($streamName);

        $I->assertSame($streamName, $streamInfo->streamName);
    }
}

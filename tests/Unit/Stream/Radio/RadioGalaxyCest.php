<?php

declare(strict_types=1);

namespace Tests\Unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\RadioGalaxy;
use Codeception\Stub;
use InvalidArgumentException;
use RuntimeException;
use Tests\Support\UnitTester;

final class RadioGalaxyCest
{
    private HttpDataFetcherInterface $httpDataFetcher;

    public function _before(): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, [
            'getJsonData' => static fn () => json_decode(
                file_get_contents(__DIR__.'/../../TestSamples/RadioGalaxySample.json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
        ]);
        $this->httpDataFetcher = $httpDataFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $starFm = new RadioGalaxy($this->httpDataFetcher);

        $I->assertInstanceOf(RadioGalaxy::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RadioGalaxy::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new RadioGalaxy($this->httpDataFetcher))->getAvailableStreams());
    }

    public function testUpdateInfo(UnitTester $I): void
    {
        $radio = new RadioGalaxy($this->httpDataFetcher);
        foreach ($radio->getAvailableStreams() as $streamName) {
            $info = $radio->getStreamInfo($streamName);

            $I->assertIsString($info->radioName);
            $I->assertIsString($info->streamName);
            $I->assertIsString($info->homepageUrl);
            $I->assertIsString($info->streamUrl);

            $I->assertIsString($info->artist);
            $I->assertIsString($info->track);

            $I->assertIsString($info->moderator);
            $I->assertIsString($info->show);
        }
    }

    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);
        $s = new RadioGalaxy($httpDataFetcher);

        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given: foobar'),
            static fn () => $s->getStreamInfo('foobar'),
        );
    }

    public function testHttpDataFetcherException(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getJsonData' => static function () {
            throw new RuntimeException('test');
        }]);

        $s = new RadioGalaxy($httpDataFetcher);

        $streamName = $s->getAvailableStreams()[0];

        $streamInfo = $s->getStreamInfo($streamName);

        $I->assertSame($streamName, $streamInfo->streamName);
    }
}

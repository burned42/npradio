<?php

declare(strict_types=1);

namespace Tests\Unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\RauteMusik;
use Codeception\Stub;
use DateTimeInterface;
use InvalidArgumentException;
use RuntimeException;
use Tests\Support\UnitTester;

final class RauteMusikCest
{
    private function getHttpDataFetcher(): HttpDataFetcherInterface
    {
        return Stub::makeEmpty(HttpDataFetcherInterface::class, ['getJsonData' => Stub::consecutive(
            json_decode(file_get_contents(
                __DIR__.'/../../TestSamples/RauteMusikClubTracksSample.json'
            ), true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(
                __DIR__.'/../../TestSamples/RauteMusikStreamsOnairSample.json'
            ), true, 512, JSON_THROW_ON_ERROR)
        )]);
    }

    public function canInstantiate(UnitTester $I): void
    {
        $rm = new RauteMusik($this->getHttpDataFetcher());

        $I->assertInstanceOf(RauteMusik::class, $rm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RauteMusik::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new RauteMusik($this->getHttpDataFetcher()))->getAvailableStreams());
    }

    public function testGetStreamInfo(UnitTester $I): void
    {
        $radio = new RauteMusik($this->getHttpDataFetcher());
        $info = $radio->getStreamInfo('RauteMusik Club');

        $I->assertIsString($info->radioName);
        $I->assertIsString($info->streamName);
        $I->assertIsString($info->homepageUrl);
        $I->assertIsString($info->streamUrl);

        $I->assertIsString($info->artist);
        $I->assertIsString($info->track);

        $I->assertInstanceOf(DateTimeInterface::class, $info->showStartTime);
        $I->assertInstanceOf(DateTimeInterface::class, $info->showEndTime);
        $I->assertIsString($info->show);
        $I->assertIsString($info->moderator);
        $I->assertEquals('DJLeTrace, Pat_Sundaze, PRIZM', $info->moderator);
    }

    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);
        $s = new RauteMusik($httpDataFetcher);

        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given: foobar'),
            static fn () => $s->getStreamInfo('foobar'),
        );
    }

    public function testHttpDataFetcherExceptionOnTrackInfo(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(
            HttpDataFetcherInterface::class,
            ['getJsonData' => static function () {
                throw new RuntimeException('test');
            }]
        );
        $s = new RauteMusik($httpDataFetcher);

        $streamName = $s->getAvailableStreams()[0];

        $streamInfo = $s->getStreamInfo($streamName);

        $I->assertSame($streamName, $streamInfo->streamName);
    }

    public function testHttpDataFetcherExceptionOnShowInfo(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(
            HttpDataFetcherInterface::class,
            ['getJsonData' => static function () {
                static $first = true;
                if ($first) {
                    $first = false;

                    return json_decode(file_get_contents(
                        __DIR__.'/../../TestSamples/RauteMusikClubTracksSample.json'
                    ), true, 512, JSON_THROW_ON_ERROR);
                }

                // throw exception on second call to test addShowInfo()
                throw new RuntimeException('test');
            }]
        );
        $s = new RauteMusik($httpDataFetcher);

        $streamName = $s->getAvailableStreams()[0];

        $streamInfo = $s->getStreamInfo($streamName);

        $I->assertSame($streamName, $streamInfo->streamName);
    }
}

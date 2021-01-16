<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\DomFetcherInterface;
use App\Stream\Radio\RauteMusik;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class RauteMusikCest
{
    /**
     * @throws Exception
     */
    private function getDomFetcher(): DomFetcherInterface
    {
        /* @var DomFetcherInterface $domFetcher */
        return Stub::makeEmpty(DomFetcherInterface::class, ['getJsonData' => Stub::consecutive(
            json_decode(file_get_contents(
                __DIR__.'/../../TestSamples/RauteMusikClubTracksSample.json'
            ), true, 512, JSON_THROW_ON_ERROR),
            json_decode(file_get_contents(
                __DIR__.'/../../TestSamples/RauteMusikStreamsOnairSample.json'
            ), true, 512, JSON_THROW_ON_ERROR)
        )]);
    }

    /**
     * @throws Exception
     */
    public function canInstantiate(UnitTester $I): void
    {
        $rm = new RauteMusik($this->getDomFetcher());

        $I->assertInstanceOf(RauteMusik::class, $rm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RauteMusik::getRadioName());
    }

    /**
     * @throws Exception
     */
    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new RauteMusik($this->getDomFetcher()))->getAvailableStreams());
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfo(UnitTester $I): void
    {
        $radio = new RauteMusik($this->getDomFetcher());
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

    /**
     * @throws Exception
     */
    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);
        $s = new RauteMusik($domFetcher);

        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given'),
            static fn () => $s->getStreamInfo('foobar'),
        );
    }

    /**
     * @throws Exception
     */
    public function testDomFetcherExceptionOnTrackInfo(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(
            DomFetcherInterface::class,
            ['getJsonData' => static function () {
                throw new RuntimeException('test');
            }]
        );
        $s = new RauteMusik($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not fetch track info: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }

    /**
     * @throws Exception
     */
    public function testDomFetcherExceptionOnShowInfo(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(
            DomFetcherInterface::class,
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
        $s = new RauteMusik($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not fetch show info: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

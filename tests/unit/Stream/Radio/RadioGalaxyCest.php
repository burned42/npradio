<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\RadioGalaxy;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class RadioGalaxyCest
{
    private HttpDataFetcherInterface $httpDataFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../../TestSamples/RadioGalaxySample.json');
        }]);
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
            new InvalidArgumentException('Invalid stream name given'),
            static fn () => $s->getStreamInfo('foobar'),
        );
    }

    /**
     * @throws Exception
     */
    public function testHttpDataFetcherException(): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getUrlContent' => static function () {
            throw new RuntimeException('test');
        }]);

        $s = new RadioGalaxy($httpDataFetcher);

        $s->getStreamInfo($s->getAvailableStreams()[0]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\SlayRadio;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class SlayRadioCest
{
    private HttpDataFetcherInterface $httpDataFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../../TestSamples/SlayRadioSample.json');
        }]);
        $this->httpDataFetcher = $httpDataFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $SlayRadio = new SlayRadio($this->httpDataFetcher);

        $I->assertInstanceOf(SlayRadio::class, $SlayRadio);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(SlayRadio::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new SlayRadio($this->httpDataFetcher))->getAvailableStreams());
    }

    public function testGetStreamInfo(UnitTester $I): void
    {
        $radio = new SlayRadio($this->httpDataFetcher);
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
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);
        $s = new SlayRadio($httpDataFetcher);

        $I->expectThrowable(
            new InvalidArgumentException('Invalid stream name given'),
            static fn () => $s->getStreamInfo('foobar'),
        );
    }

    /**
     * @throws Exception
     */
    public function testHttpDataFetcherException(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getUrlContent' => static function () {
            throw new RuntimeException('test');
        }]);
        $s = new SlayRadio($httpDataFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get url content: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

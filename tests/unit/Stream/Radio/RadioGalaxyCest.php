<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\DomFetcherInterface;
use App\Stream\Radio\RadioGalaxy;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class RadioGalaxyCest
{
    private DomFetcherInterface $domFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class, ['getUrlContent' => static function () {
            return file_get_contents(__DIR__.'/../../TestSamples/RadioGalaxySample.json');
        }]);
        $this->domFetcher = $domFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $starFm = new RadioGalaxy($this->domFetcher);

        $I->assertInstanceOf(RadioGalaxy::class, $starFm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RadioGalaxy::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new RadioGalaxy($this->domFetcher))->getAvailableStreams());
    }

    public function testUpdateInfo(UnitTester $I): void
    {
        $radio = new RadioGalaxy($this->domFetcher);
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
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);
        $s = new RadioGalaxy($domFetcher);

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
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class, ['getUrlContent' => static function () {
            throw new RuntimeException('test');
        }]);

        $s = new RadioGalaxy($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get url content: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

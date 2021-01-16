<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\TechnoBase;
use App\Tests\UnitTester;
use Codeception\Exception\TestRuntimeException;
use Codeception\Util\Stub;
use DateTimeInterface;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class TechnoBaseCest
{
    private HttpDataFetcherInterface $httpDataFetcher;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getXmlDom' => static function () {
            $dom = new DOMDocument();
            $xml = file_get_contents(__DIR__.'/../../TestSamples/TechnoBaseSample.xml');
            @$dom->loadXML($xml);

            return $dom;
        }]);
        $this->httpDataFetcher = $httpDataFetcher;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $tb = new TechnoBase($this->httpDataFetcher);

        $I->assertInstanceOf(TechnoBase::class, $tb);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotNull(TechnoBase::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new TechnoBase($this->httpDataFetcher))->getAvailableStreams());
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfo(UnitTester $I): void
    {
        $radio = new TechnoBase($this->httpDataFetcher);
        foreach ($radio->getAvailableStreams() as $streamName) {
            $info = $radio->getStreamInfo($streamName);

            $I->assertIsString($info->radioName);
            $I->assertIsString($info->streamName);
            $I->assertIsString($info->homepageUrl);
            $I->assertIsString($info->streamUrl);

            $I->assertIsString($info->artist);
            $I->assertIsString($info->track);
        }

        // Test additional properties with a stream where the test sample has data
        $info = $radio->getStreamInfo('TechnoBase.FM');
        $I->assertIsString($info->moderator);
        $I->assertIsString($info->show);
        $I->assertIsString($info->genre);
        $I->assertInstanceOf(DateTimeInterface::class, $info->showStartTime);
        $I->assertInstanceOf(DateTimeInterface::class, $info->showEndTime);
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);
        $s = new TechnoBase($httpDataFetcher);

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
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getXmlDom' => static function () {
            throw new TestRuntimeException('test');
        }]);
        $s = new TechnoBase($httpDataFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get xml dom: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

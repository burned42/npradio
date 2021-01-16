<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\Radio\MetalOnly;
use App\Stream\StreamInfo;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use RuntimeException;

final class MetalOnlyCest
{
    private HttpDataFetcherInterface $httpDataFetcher;
    private HttpDataFetcherInterface $httpDataFetcherNotOnAir;
    private HttpDataFetcherInterface $httpDataFetcherOnAir;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        $httpDataFetcher = Stub::makeEmpty(
            HttpDataFetcherInterface::class,
            ['getHtmlDom' => static function () {
                $dom = new DOMDocument();
                $html = file_get_contents(__DIR__.'/../../TestSamples/MetalOnlySample.html');
                @$dom->loadHTML($html);

                return $dom;
            }]
        );
        $this->httpDataFetcher = $httpDataFetcher;

        $httpDataFetcherNotOnAir = Stub::makeEmpty(
            HttpDataFetcherInterface::class,
            ['getHtmlDom' => static function () {
                $dom = new DOMDocument();
                $html = file_get_contents(__DIR__.'/../../TestSamples/MetalOnlySampleNotOnAir.html');
                @$dom->loadHTML($html);

                return $dom;
            }]
        );
        $this->httpDataFetcherNotOnAir = $httpDataFetcherNotOnAir;

        $httpDataFetcherOnAir = Stub::makeEmpty(
            HttpDataFetcherInterface::class,
            ['getHtmlDom' => static function () {
                $dom = new DOMDocument();
                $html = file_get_contents(__DIR__.'/../../TestSamples/MetalOnlySampleOnAir.html');
                @$dom->loadHTML($html);

                return $dom;
            }]
        );
        $this->httpDataFetcherOnAir = $httpDataFetcherOnAir;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $mo = new MetalOnly($this->httpDataFetcher);

        $I->assertInstanceOf(MetalOnly::class, $mo);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(MetalOnly::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new MetalOnly($this->httpDataFetcher))->getAvailableStreams());
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfo(UnitTester $I): void
    {
        $mo = new MetalOnly($this->httpDataFetcher);

        foreach ($mo->getAvailableStreams() as $streamName) {
            $info = $mo->getStreamInfo($streamName);
            $I->assertInstanceOf(StreamInfo::class, $info);

            $I->assertIsString($info->radioName);
            $I->assertIsString($info->streamName);
            $I->assertIsString($info->homepageUrl);
            $I->assertIsString($info->streamUrl);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetInfoOnAir(UnitTester $I): void
    {
        $mo = new MetalOnly($this->httpDataFetcherOnAir);

        foreach ($mo->getAvailableStreams() as $streamName) {
            $info = $mo->getStreamInfo($streamName);

            $I->assertNotNull($info->moderator);
            $I->assertNotNull($info->show);
            $I->assertNotNull($info->genre);

            $I->assertNotNull($info->artist);
            $I->assertNotNull($info->track);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetInfoNotOnAir(UnitTester $I): void
    {
        $mo = new MetalOnly($this->httpDataFetcherNotOnAir);

        foreach ($mo->getAvailableStreams() as $streamName) {
            $info = $mo->getStreamInfo($streamName);

            $I->assertNull($info->moderator);
            $I->assertNull($info->show);
            $I->assertNull($info->genre);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);
        $s = new MetalOnly($httpDataFetcher);

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
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class, ['getHtmlDom' => static function () {
            throw new RuntimeException('test');
        }]);
        $mo = new MetalOnly($httpDataFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get html dom: test'),
            static fn () => $mo->getStreamInfo($mo->getAvailableStreams()[0]),
        );
    }
}

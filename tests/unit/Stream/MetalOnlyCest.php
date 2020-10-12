<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\MetalOnly;
use App\Stream\StreamInfo;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class MetalOnlyCest
{
    private HttpDomFetcher $domFetcher;
    private HttpDomFetcher $domFetcherNotOnAir;
    private HttpDomFetcher $domFetcherOnAir;

    /**
     * @throws Exception
     */
    public function _before(): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => static function () {
            $dom = new DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySample.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);
        $this->domFetcher = $domFetcher;

        /** @var HttpDomFetcher $domFetcherNotOnAir */
        $domFetcherNotOnAir = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => static function () {
            $dom = new DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySampleNotOnAir.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);
        $this->domFetcherNotOnAir = $domFetcherNotOnAir;

        /** @var HttpDomFetcher $domFetcherOnAir */
        $domFetcherOnAir = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => static function () {
            $dom = new DOMDocument();
            $html = file_get_contents(__DIR__.'/../TestSamples/MetalOnlySampleOnAir.html');
            @$dom->loadHTML($html);

            return $dom;
        }]);
        $this->domFetcherOnAir = $domFetcherOnAir;
    }

    public function canInstantiate(UnitTester $I): void
    {
        $mo = new MetalOnly($this->domFetcher);

        $I->assertInstanceOf(MetalOnly::class, $mo);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(MetalOnly::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty((new MetalOnly($this->domFetcher))->getAvailableStreams());
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfo(UnitTester $I): void
    {
        $mo = new MetalOnly($this->domFetcher);

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
        $mo = new MetalOnly($this->domFetcherOnAir);

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
        $mo = new MetalOnly($this->domFetcherNotOnAir);

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
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class);
        $s = new MetalOnly($domFetcher);

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
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => static function () {
            throw new RuntimeException('test');
        }]);
        $mo = new MetalOnly($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get html dom: test'),
            static fn () => $mo->getStreamInfo($mo->getAvailableStreams()[0]),
        );
    }
}

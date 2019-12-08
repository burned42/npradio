<?php

declare(strict_types=1);

namespace App\Tests\Unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\MetalOnly;
use Codeception\Util\Stub;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use UnitTester;

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
        $mo = new MetalOnly($this->domFetcher, MetalOnly::getAvailableStreams()[0]);

        $I->assertInstanceOf(MetalOnly::class, $mo);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(MetalOnly::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        $I->assertNotEmpty(MetalOnly::getAvailableStreams());
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testUpdateInfo(UnitTester $I): void
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            new MetalOnly($this->domFetcher, $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testGetInfoOnAir(UnitTester $I): void
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            $mo = new MetalOnly($this->domFetcherOnAir, $streamName);

            $I->assertNotNull($mo->getModerator());
            $I->assertNotNull($mo->getShow());
            $I->assertNotNull($mo->getGenre());
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function testGetInfoNotOnAir(UnitTester $I): void
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            $mo = new MetalOnly($this->domFetcherNotOnAir, $streamName);

            $I->assertNull($mo->getModerator());
            $I->assertNull($mo->getShow());
            $I->assertNull($mo->getGenre());
        }
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

        $I->expectThrowable(
            new RuntimeException('could not get html dom: test'),
            static function () use ($domFetcher) {
                new MetalOnly($domFetcher, MetalOnly::getAvailableStreams()[0]);
            }
        );
    }

    public function testProtectedMethods(UnitTester $I): void
    {
        $mo = new MetalOnly($this->domFetcher, MetalOnly::getAvailableStreams()[0]);
        $info = $mo->getAsArray();

        $I->assertNotEmpty($info['homepage']);
        $I->assertInternalType('string', $info['homepage']);

        $I->assertNotEmpty($info['stream_url']);
        $I->assertInternalType('string', $info['stream_url']);
    }
}

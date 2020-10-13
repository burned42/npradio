<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\RauteMusik;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use DateTimeInterface;
use DOMDocument;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class RauteMusikCest
{
    /**
     * @return HttpDomFetcher|object
     *
     * @throws Exception
     */
    private function getDomFetcher()
    {
        $trackInfoDom = new DOMDocument();
        $html = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubTrackInfoSample.html');
        libxml_use_internal_errors(true);
        $trackInfoDom->loadHTML($html);

        $showInfoDom = new DOMDocument();
        $html = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubShowInfoSample.html');
        libxml_use_internal_errors(true);
        $showInfoDom->loadHTML($html);

        /* @var HttpDomFetcher $domFetcher */
        return Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => Stub::consecutive(
            $trackInfoDom,
            $showInfoDom
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
        foreach ($radio->getAvailableStreams() as $streamName) {
            // re-instantiate to get a fresh DomFetcher mock
            $r = new RauteMusik($this->getDomFetcher());
            $info = $r->getStreamInfo($streamName);

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
        }
    }

    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class);
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
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => static function () {
            throw new RuntimeException('test');
        }]);
        $s = new RauteMusik($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get html dom: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }

    /**
     * @throws Exception
     */
    public function testDomFetcherExceptionOnShowInfo(UnitTester $I): void
    {
        /** @var HttpDomFetcher $domFetcher */
        $domFetcher = Stub::makeEmpty(HttpDomFetcher::class, ['getHtmlDom' => static function () {
            static $first = true;
            if ($first) {
                $first = false;

                $trackInfoDom = new DOMDocument();
                $xml = file_get_contents(__DIR__.'/../TestSamples/RauteMusikClubTrackInfoSample.html');
                @$trackInfoDom->loadXML($xml);

                return $trackInfoDom;
            }

            // throw exception on second call to test fetchShowInfo()
            throw new RuntimeException('test');
        }]);
        $s = new RauteMusik($domFetcher);

        $I->expectThrowable(
            new RuntimeException('could not get html dom: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

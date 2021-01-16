<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream\Radio;

use App\DataFetcher\DomFetcherInterface;
use App\Stream\Radio\RauteMusik;
use App\Tests\UnitTester;
use BadMethodCallException;
use Codeception\Util\Stub;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class RauteMusikCest
{
    private function getHttpClientMock(): MockHttpClient
    {
        return new MockHttpClient(function ($method, $url, $options) {
            if ('https://api.rautemusik.fm/streams/club/tracks/' === $url) {
                $data = file_get_contents(
                    __DIR__.'/../../TestSamples/RauteMusikClubTracksSample.json'
                );
            } elseif ('https://api.rautemusik.fm/streams_onair/' === $url) {
                $data = file_get_contents(
                    __DIR__.'/../../TestSamples/RauteMusikStreamsOnairSample.json'
                );
            } else {
                throw new BadMethodCallException('unknown url called: '.$url);
            }

            return new MockResponse($data);
        });
    }

    /**
     * @throws Exception
     */
    public function canInstantiate(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);

        $rm = new RauteMusik($domFetcher, $this->getHttpClientMock());

        $I->assertInstanceOf(RauteMusik::class, $rm);
    }

    public function testRadioNameSet(UnitTester $I): void
    {
        $I->assertNotEmpty(RauteMusik::getRadioName());
    }

    public function testStreamsSet(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);

        $I->assertNotEmpty((new RauteMusik($domFetcher, $this->getHttpClientMock()))->getAvailableStreams());
    }

    /**
     * @throws Exception
     */
    public function testGetStreamInfo(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);
        $httpClient = $this->getHttpClientMock();

        $radio = new RauteMusik($domFetcher, $httpClient);
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
    }

    public function testGetStreamInfoExceptionOnInvalidStreamName(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);
        $s = new RauteMusik($domFetcher, $this->getHttpClientMock());

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
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            throw new RuntimeException('test');
        });
        $s = new RauteMusik($domFetcher, $httpClient);

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
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);
        $httpClient = new MockHttpClient(function ($method, $url, $options) {
            if ('https://api.rautemusik.fm/streams/main/tracks/' === $url) {
                $data = file_get_contents(
                    __DIR__.'/../../TestSamples/RauteMusikClubTracksSample.json'
                );
            } else {
                // throw exception to test addShowInfo()
                throw new RuntimeException('test');
            }

            return new MockResponse($data);
        });
        $s = new RauteMusik($domFetcher, $httpClient);

        $I->expectThrowable(
            new RuntimeException('could not fetch show info: test'),
            static fn () => $s->getStreamInfo($s->getAvailableStreams()[0]),
        );
    }
}

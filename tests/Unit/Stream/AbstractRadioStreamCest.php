<?php

declare(strict_types=1);

namespace Tests\Unit\Stream;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use Codeception\Stub;
use Tests\Support\UnitTester;

final class DummyRadioStream extends AbstractRadioStream
{
    public static function getRadioName(): string
    {
        return 'foo';
    }

    public function getAvailableStreams(): array
    {
        return ['bar'];
    }

    public function getStreamInfo(string $streamName): StreamInfo
    {
        return Stub::make(StreamInfo::class);
    }
}

class AbstractRadioStreamCest
{
    public function testConstructor(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);

        $radioStream = new DummyRadioStream($httpDataFetcher);

        $I->assertInstanceOf(AbstractRadioStream::class, $radioStream);
    }
}

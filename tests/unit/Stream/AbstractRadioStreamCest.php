<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\HttpDataFetcherInterface;
use App\Stream\AbstractRadioStream;
use App\Stream\StreamInfo;
use App\Tests\UnitTester;
use Codeception\Util\Stub;
use Exception;

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

    /**
     * @throws Exception
     */
    public function getStreamInfo(string $streamName): StreamInfo
    {
        return Stub::make(StreamInfo::class);
    }
}

class AbstractRadioStreamCest
{
    /**
     * @throws Exception
     */
    public function testConstructor(UnitTester $I): void
    {
        $httpDataFetcher = Stub::makeEmpty(HttpDataFetcherInterface::class);

        $radioStream = new DummyRadioStream($httpDataFetcher);

        $I->assertInstanceOf(AbstractRadioStream::class, $radioStream);
    }
}

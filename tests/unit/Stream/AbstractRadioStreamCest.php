<?php

declare(strict_types=1);

namespace App\Tests\unit\Stream;

use App\DataFetcher\DomFetcherInterface;
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
        /** @var StreamInfo $streamInfo */
        $streamInfo = Stub::make(StreamInfo::class);

        return $streamInfo;
    }
}

class AbstractRadioStreamCest
{
    public function testConstructor(UnitTester $I): void
    {
        /** @var DomFetcherInterface $domFetcher */
        $domFetcher = Stub::makeEmpty(DomFetcherInterface::class);

        $radioStream = new DummyRadioStream($domFetcher);

        $I->assertInstanceOf(AbstractRadioStream::class, $radioStream);
    }
}

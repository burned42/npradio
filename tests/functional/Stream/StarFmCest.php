<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\StarFm;
use App\Stream\StreamInfo;
use App\Tests\FunctionalTester;

class StarFmCest
{
    public function testWithLiveData(FunctionalTester $I): void
    {
        $r = new StarFm(new HttpDomFetcher());
        foreach ($r->getAvailableStreams() as $streamName) {
            $I->assertInstanceOf(
                StreamInfo::class,
                $r->getStreamInfo($streamName)
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\SlayRadio;
use App\Stream\StreamInfo;
use App\Tests\FunctionalTester;

class SlayRadioCest
{
    public function testWithLiveData(FunctionalTester $I): void
    {
        $r = new SlayRadio(new HttpDomFetcher());
        foreach ($r->getAvailableStreams() as $streamName) {
            $I->assertInstanceOf(
                StreamInfo::class,
                $r->getStreamInfo($streamName)
            );
        }
    }
}

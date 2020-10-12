<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\RauteMusik;
use App\Stream\StreamInfo;
use App\Tests\FunctionalTester;
use Exception;

class RauteMusikCest
{
    /**
     * @throws Exception
     */
    public function testWithLiveData(FunctionalTester $I): void
    {
        $r = new RauteMusik(new HttpDomFetcher());
        foreach ($r->getAvailableStreams() as $streamName) {
            $I->assertInstanceOf(
                StreamInfo::class,
                $r->getStreamInfo($streamName)
            );
        }
    }
}

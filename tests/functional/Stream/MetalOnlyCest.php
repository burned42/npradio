<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\MetalOnly;
use App\Stream\StreamInfo;
use App\Tests\FunctionalTester;
use Exception;

class MetalOnlyCest
{
    /**
     * @throws Exception
     */
    public function testWithLiveData(FunctionalTester $I): void
    {
        $r = new MetalOnly(new HttpDomFetcher());
        foreach ($r->getAvailableStreams() as $streamName) {
            $I->assertInstanceOf(
                StreamInfo::class,
                $r->getStreamInfo($streamName)
            );
        }
    }
}

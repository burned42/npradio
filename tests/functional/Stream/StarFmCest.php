<?php

declare(strict_types=1);

namespace App\Tests\Functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\StarFm;

class StarFmCest
{
    /**
     * @param \FunctionalTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testWithLiveData(\FunctionalTester $I): void
    {
        foreach (StarFm::getAvailableStreams() as $streamName) {
            new StarFm(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}

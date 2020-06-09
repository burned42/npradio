<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\StarFm;
use App\Tests\FunctionalTester;
use InvalidArgumentException;
use RuntimeException;

class StarFmCest
{
    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testWithLiveData(FunctionalTester $I): void
    {
        foreach (StarFm::getAvailableStreams() as $streamName) {
            new StarFm(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}

<?php

declare(strict_types=1);

namespace Stream;

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\StarFm;

class StarFmCest
{
    /**
     * @param \FunctionalTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testWithLiveData(\FunctionalTester $I)
    {
        foreach (StarFm::getAvailableStreams() as $streamName) {
            new StarFm(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}
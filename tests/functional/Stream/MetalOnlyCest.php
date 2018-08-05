<?php

declare(strict_types=1);

namespace Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\MetalOnly;

class MetalOnlyCest
{
    /**
     * @param \FunctionalTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testWithLiveData(\FunctionalTester $I)
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            new MetalOnly(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}

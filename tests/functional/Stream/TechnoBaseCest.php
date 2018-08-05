<?php

declare(strict_types=1);

namespace Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\TechnoBase;

class TechnoBaseCest
{
    /**
     * @param \FunctionalTester $I
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function testWithLiveData(\FunctionalTester $I)
    {
        foreach (TechnoBase::getAvailableStreams() as $streamName) {
            new TechnoBase(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\MetalOnly;
use FunctionalTester;
use InvalidArgumentException;
use RuntimeException;

class MetalOnlyCest
{
    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testWithLiveData(FunctionalTester $I): void
    {
        foreach (MetalOnly::getAvailableStreams() as $streamName) {
            new MetalOnly(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}

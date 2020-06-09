<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\SlayRadio;
use App\Tests\FunctionalTester;
use InvalidArgumentException;
use RuntimeException;

class SlayRadioCest
{
    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function testWithLiveData(FunctionalTester $I): void
    {
        foreach (SlayRadio::getAvailableStreams() as $streamName) {
            new SlayRadio(new HttpDomFetcher(), $streamName);
        }

        // dummy assertion, updateInfo() just shall not throw an exception so
        // if we get here everything is ok
        $I->assertTrue(true);
    }
}

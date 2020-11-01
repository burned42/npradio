<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use App\Stream\MetalOnly;
use App\Stream\RadioGalaxy;
use App\Stream\RauteMusik;
use App\Stream\SlayRadio;
use App\Stream\StarFm;
use App\Stream\TechnoBase;
use Codeception\Example;
use Exception;
use Generator;

final class RadioStreamCest
{
    private const RADIOS = [
        MetalOnly::class,
        RadioGalaxy::class,
        RauteMusik::class,
        SlayRadio::class,
        StarFm::class,
        TechnoBase::class,
    ];

    /**
     * @dataProvider getExamples
     *
     * @throws Exception
     */
    public function testGetStreamInfoWithLiveData(Example $example): void
    {
        /** @var AbstractRadioStream $radio */
        [$radio, $stream] = $example;

        $radio->getStreamInfo($stream);
    }

    private function getExamples(): Generator
    {
        foreach (self::RADIOS as $radioClass) {
            $radio = new $radioClass(new HttpDomFetcher());

            foreach ($radio->getAvailableStreams() as $streamName) {
                yield [$radio, $streamName];
            }
        }
    }
}

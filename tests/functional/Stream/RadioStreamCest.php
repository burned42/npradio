<?php

declare(strict_types=1);

namespace App\Tests\functional\Stream;

use App\DataFetcher\HttpDomFetcher;
use App\Stream\AbstractRadioStream;
use App\Stream\Radio\MetalOnly;
use App\Stream\Radio\RadioGalaxy;
use App\Stream\Radio\RauteMusik;
use App\Stream\Radio\SlayRadio;
use App\Stream\Radio\StarFm;
use App\Stream\Radio\TechnoBase;
use Codeception\Example;
use Exception;
use Generator;
use Symfony\Component\HttpClient\HttpClient;

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
            if (RauteMusik::class === $radioClass) {
                $radio = new $radioClass(
                    new HttpDomFetcher(),
                    HttpClient::create()
                );
            } else {
                $radio = new $radioClass(new HttpDomFetcher());
            }

            foreach ($radio->getAvailableStreams() as $streamName) {
                yield [$radio, $streamName];
            }
        }
    }
}

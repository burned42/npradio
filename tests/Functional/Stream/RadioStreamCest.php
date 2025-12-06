<?php

declare(strict_types=1);

namespace Tests\Functional\Stream;

use App\Stream\AbstractRadioStream;
use App\Stream\Radio\MetalOnly;
use App\Stream\Radio\RadioGalaxy;
use App\Stream\Radio\RauteMusik;
use App\Stream\Radio\SlayRadio;
use App\Stream\Radio\StarFm;
use App\Stream\Radio\TechnoBase;
use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Generator;
use ReflectionClass;
use Tests\Support\FunctionalTester;

final class RadioStreamCest
{
    private const array RADIOS = [
        MetalOnly::class,
        RadioGalaxy::class,
        RauteMusik::class,
        SlayRadio::class,
        StarFm::class,
        TechnoBase::class,
    ];

    #[DataProvider('getExamples')]
    public function testGetStreamInfoWithLiveData(FunctionalTester $I, Example $example): void
    {
        [$radioClass, $stream] = $example;

        /** @var AbstractRadioStream $radio */
        $radio = $I->grabService($radioClass);

        $info = $radio->getStreamInfo($stream);

        $I->assertIsString($info->track);
        $I->assertIsString($info->artist);
    }

    private function getExamples(): Generator
    {
        foreach (self::RADIOS as $radioClass) {
            $radio = new ReflectionClass($radioClass)
                ->newInstanceWithoutConstructor();

            foreach ($radio->getAvailableStreams() as $streamName) {
                yield [$radioClass, $streamName];
            }
        }
    }
}

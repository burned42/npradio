<?php

declare(strict_types=1);

namespace App\Tests\functional\Command;

use App\Tests\FunctionalTester;
use Codeception\Example;
use Exception;
use InvalidArgumentException;

class StreamInfoCommandCest
{
    /**
     * @example [null, null, ["Metal Only", "RauteMusik Club", "TeaTime.FM"], []]
     * @example ["TechnoBase.FM", null, ["HouseTime.FM", "HardBase.FM"], ["Metal Only", "RauteMusik"]]
     * @example ["TechnoBase.FM", "HouseTime.FM", ["HouseTime.FM"], ["HardBase.FM", "RauteMusik"]]
     *
     * @throws Exception
     */
    public function testCommand(FunctionalTester $I, Example $example): void
    {
        $parameters = [];
        if (null !== $example[0]) {
            $parameters['radio-name'] = $example[0];
        }
        if (null !== $example[1]) {
            $parameters['stream-name'] = $example[1];
        }

        $output = $I->runSymfonyConsoleCommand('app:stream-info', $parameters);

        foreach ($example[2] as $expectedOutput) {
            $I->assertStringContainsString($expectedOutput, $output);
        }
        foreach ($example[3] as $unexpectedOutput) {
            $I->assertStringNotContainsString($unexpectedOutput, $output);
        }
    }

    /**
     * @example [{"radio-name": "foobar"}, "Invalid radio name given"]
     * @example [{"radio-name": "STAR FM", "stream-name": "foobar"}, "Invalid stream name given"]
     */
    public function testExceptionOnInvalidArguments(FunctionalTester $I, Example $example): void
    {
        $I->expectThrowable(
            new InvalidArgumentException($example[1]),
            fn () => $I->runSymfonyConsoleCommand('app:stream-info', $example[0])
        );
    }
}

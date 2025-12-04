<?php

declare(strict_types=1);

namespace Tests\Functional\Command;

use Codeception\Attribute\Examples;
use Codeception\Example;
use InvalidArgumentException;
use Tests\Support\FunctionalTester;

class StreamInfoCommandCest
{
    #[Examples(null, null, ['Metal Only', 'RauteMusik Club', 'HouseTime.FM'], [])]
    #[Examples('TechnoBase.FM', null, ['HouseTime.FM', 'HardBase.FM'], ['Metal Only', 'RauteMusik'])]
    #[Examples('TechnoBase.FM', 'HouseTime.FM', ['HouseTime.FM'], ['HardBase.FM', 'RauteMusik'])]
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

    #[Examples(['radio-name' => 'foobar'], 'Invalid radio name given: foobar')]
    #[Examples(['radio-name' => 'STAR FM', 'stream-name' => 'foobar'], 'Invalid stream name given')]
    public function testExceptionOnInvalidArguments(FunctionalTester $I, Example $example): void
    {
        $I->expectThrowable(
            new InvalidArgumentException($example[1]),
            fn () => $I->runSymfonyConsoleCommand('app:stream-info', $example[0])
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\DependencyInjection\Compiler;

use App\Controller\ApiController;
use App\DependencyInjection\Compiler\StreamPass;
use App\Stream\AbstractRadioStream;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use UnitTester;

class StreamPassCest
{
    /** @var StreamPass */
    private $streamPass;

    public function _before(): void
    {
        $this->streamPass = new StreamPass();
    }

    public function canInstantiate(UnitTester $I): void
    {
        $I->assertInstanceOf(StreamPass::class, $this->streamPass);
    }

    public function testCanProcessEmptyContainer(UnitTester $I): void
    {
        $container = new ContainerBuilder();

        $this->streamPass->process($container);

        $I->assertTrue(true, 'no error on processing');
    }

    public function testCanProcessFilledContainer(UnitTester $I): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(ApiController::class, new Definition(ApiController::class));
        $container->register(AbstractRadioStream::class)
            ->addTag('app.stream');

        $this->streamPass->process($container);

        $I->assertTrue(true, 'no error on processing');
    }
}

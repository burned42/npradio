<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Controller\ApiController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StreamPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ApiController::class)) {
            return;
        }

        $definition = $container->findDefinition(ApiController::class);

        $streamServices = $container->findTaggedServiceIds('app.stream');

        foreach (array_keys($streamServices) as $id) {
            $definition->addMethodCall('addRadio', [$id]);
        }
    }
}

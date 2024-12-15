<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;

return RectorConfig::configure()
    ->withPhpSets()
    ->withImportNames()
    ->withPreparedSets(
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withAttributesSets(symfony: true)
    ->withSets([
        SymfonySetList::CONFIGS,
        SymfonySetList::SYMFONY_72,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        TwigSetList::TWIG_24,
        TwigSetList::TWIG_UNDERSCORE_TO_NAMESPACE,
    ]);

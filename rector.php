<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    // get parameters
    // $parameters = $rectorConfig->parameters();

    // Define what rule sets will be applied
    $rectorConfig->import(LevelSetList::UP_TO_PHP_82);
    $rectorConfig->import(SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(SetList::PSR_4);
    $rectorConfig->import(SymfonyLevelSetList::UP_TO_SYMFONY_62);
    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $rectorConfig->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(SymfonySetList::SYMFONY_STRICT);
    $rectorConfig->import(TwigLevelSetList::UP_TO_TWIG_240);

    // get services (needed for register a single rule)
    // $services = $rectorConfig->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};

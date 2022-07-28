<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    // get parameters
    // $parameters = $rectorConfig->parameters();

    // Define what rule sets will be applied
    $rectorConfig->import(SetList::PSR_4);
    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(SymfonyLevelSetList::UP_TO_SYMFONY_60);

    // get services (needed for register a single rule)
    // $services = $rectorConfig->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};

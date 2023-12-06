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
    $rectorConfig->import(LevelSetList::UP_TO_PHP_83);
    $rectorConfig->import(SetList::EARLY_RETURN);
    $rectorConfig->import(SetList::INSTANCEOF);
    $rectorConfig->import(SetList::PRIVATIZATION);
    $rectorConfig->import(SetList::STRICT_BOOLEANS);
    $rectorConfig->import(SetList::TYPE_DECLARATION);
    $rectorConfig->import(SymfonyLevelSetList::UP_TO_SYMFONY_63);
    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $rectorConfig->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(TwigLevelSetList::UP_TO_TWIG_240);

    // get services (needed for register a single rule)
    // $services = $rectorConfig->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};

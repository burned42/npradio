<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Set\TwigSetList;

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
    $rectorConfig->import(SymfonySetList::CONFIGS);
    $rectorConfig->import(SymfonySetList::SYMFONY_64);
    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $rectorConfig->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $rectorConfig->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(TwigSetList::TWIG_240);
    $rectorConfig->import(TwigSetList::TWIG_UNDERSCORE_TO_NAMESPACE);

    // get services (needed for register a single rule)
    // $services = $rectorConfig->services();

    // register a single rule
    // $services->set(TypedPropertyRector::class);
};

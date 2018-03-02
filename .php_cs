<?php

declare(strict_types=1);

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([
                'src',
                'tests/acceptance',
                'tests/functional',
                'tests/unit',
                'web',
            ])
            ->notName('_bootstrap.php')
    );

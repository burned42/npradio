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
                'web',
                'tests/acceptance',
                    'tests/functional',
                'tests/unit',
            ])
            ->notName('_bootstrap.php')
    );

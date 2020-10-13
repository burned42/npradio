<?php

declare(strict_types=1);

require_once 'vendor/symfony/dotenv/Dotenv.php';

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if (
    'prod' === $_SERVER['APP_ENV']
    && file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')
) {
    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}

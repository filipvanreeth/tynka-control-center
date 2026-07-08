<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();

return [
    'paths' => [
        'migrations' => BASE_PATH . '/db/migrations',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'sqlite',
            'name' => BASE_PATH . '/storage/database',
            'suffix' => '.sqlite',
        ],
    ],
];

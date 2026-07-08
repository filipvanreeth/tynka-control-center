<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/*
 * Gedeelde bootstrap: autoload, paden, env en de DI-container. Kanaal-neutraal —
 * bewust géén sessie hier (web-specifiek; de API en de latere console zijn
 * stateless). Elk entrypoint (`public/index.php`, straks `bin/console`) requiret
 * dit bestand en krijgt dezelfde, gebouwde container terug.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

/** @var ContainerInterface $container */
$container = (new ContainerBuilder())
    ->addDefinitions(BASE_PATH . '/config/container.php')
    ->build();

return $container;

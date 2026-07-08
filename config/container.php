<?php

declare(strict_types=1);

use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Infrastructure\Persistence\PdoAccountRepository;
use TynkaControlCenter\CheckIn\Application\Query\CheckInReadModel;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategoryRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInRepository;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInActivityCategoryRepository;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInActivityRepository;
use TynkaControlCenter\CheckIn\Infrastructure\Persistence\PdoCheckInReadModel;
use TynkaControlCenter\CheckIn\Infrastructure\Persistence\PdoCheckInRepository;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Common\Infrastructure\FileTranslator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Domain\HandlerRepository;
use TynkaControlCenter\Handler\Infrastructure\InMemoryHandlerRepository;
use TynkaControlCenter\Infrastructure\Http\PhpSession;
use TynkaControlCenter\Infrastructure\Http\Session;
use TynkaControlCenter\Infrastructure\Templating\PhpTemplateEngine;
use TynkaControlCenter\Infrastructure\Templating\TemplateEngine;

use function DI\autowire;
use function DI\factory;
use function FastRoute\simpleDispatcher;

/*
 * PHP-DI container-definities. Enkel wat autowiring niet kan raden staat hier:
 * de scalar-argumenten (env, paden, tabelnamen) en de poort → adapter bindings.
 * Alle handlers en de controller worden automatisch bedraad (autowiring).
 */
return [
    // --- Configuratie ------------------------------------------------------
    AppConfig::class => factory(static function (): AppConfig {
        return new AppConfig(
            appUrl: $_ENV['APP_URL'] ?? 'http://localhost:80',
            appVersion: '0.1.9',
            dbDriver: $_ENV['DB_DRIVER'] ?? 'sqlite',
            dbDatabase: BASE_PATH . '/storage/database.sqlite',
        );
    }),

    // --- Database ----------------------------------------------------------
    PDO::class => factory(static function (ContainerInterface $container): PDO {
        $config = $container->get(AppConfig::class);

        return new PDO("{$config->dbDriver}:{$config->dbDatabase}");
    }),

    // --- Routing -----------------------------------------------------------
    Dispatcher::class => factory(static function (): Dispatcher {
        return simpleDispatcher(require BASE_PATH . '/config/routes.php');
    }),

    // --- Poorten → Adapters ------------------------------------------------
    AccountRepository::class => autowire(PdoAccountRepository::class)
        ->constructorParameter('tableName', 'accounts'),

    CheckInRepository::class => autowire(PdoCheckInRepository::class)
        ->constructorParameter('tableName', 'checkins'),

    CheckInReadModel::class => autowire(PdoCheckInReadModel::class)
        ->constructorParameter('tableName', 'checkins'),

    CheckInActivityRepository::class => autowire(InMemoryCheckInActivityRepository::class),

    CheckInActivityCategoryRepository::class => autowire(InMemoryCheckInActivityCategoryRepository::class),

    HandlerRepository::class => autowire(InMemoryHandlerRepository::class),

    Translator::class => autowire(FileTranslator::class)
        ->constructorParameter('languagePath', BASE_PATH . '/resources/lang'),

    Session::class => autowire(PhpSession::class),

    TemplateEngine::class => autowire(PhpTemplateEngine::class)
        ->constructorParameter('templatePath', BASE_PATH),
];

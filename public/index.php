<?php
declare(strict_types=1);

use TynkaControlCenter\Presentation\ViewRenderer;
use \TynkaControlCenter\Services\CheckInService;
use \TynkaControlCenter\Services\TranslationService;
use \TynkaControlCenter\Controllers\CheckInController;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$appConfig = new \TynkaControlCenter\Config\AppConfig(
    appUrl: $_ENV['APP_URL'] ?? 'http://localhost:80',
    appVersion: '0.1.9',
    dbDriver: $_ENV['DB_DRIVER'] ?? 'sqlite',
    dbDatabase: __DIR__ . '/../storage/database.sqlite',
);

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/resources/views/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $routeCollector): void {
    $routeCollector->addRoute('POST', '/checkin', 'check-in.submit');
    $routeCollector->addRoute('GET', '/', 'check-ins.index');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

$pdo = new PDO(
    "{$appConfig->dbDriver}:{$appConfig->dbDatabase}"
);

$checkInRepository = new \TynkaControlCenter\Repositories\CheckInRepository($pdo, 'checkins');
$handlerService = new \TynkaControlCenter\Services\HandlerService();
$translationService = new TranslationService($appConfig->locale, 'en');
$checkInService = new CheckInService($checkInRepository);
$checkInController = new CheckInController(
    checkInService: $checkInService,
    handlerService: $handlerService,
    translationService: $translationService,
    appConfig: $appConfig,
    viewRenderer: new ViewRenderer($appConfig)
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        // var_dump("404 Not Found");
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        // var_dump("405 Method Not Allowed");

        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        match ($handler) {
            'check-in.submit' => $checkInController->handleCheckInSubmission(),
            'check-ins.index' => $checkInController->showCheckIns(),
            default => throw new RuntimeException("Unknown route handler: $handler"),
        };

        break;
}

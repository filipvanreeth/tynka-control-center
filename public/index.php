<?php
declare(strict_types=1);

use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInOptionsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInHandler;
use TynkaControlCenter\CheckIn\Presentation\CheckInController;
use TynkaControlCenter\Common\Infrastructure\FileTranslator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Handler\Infrastructure\InMemoryHandlerRepository;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInHandler;
use TynkaControlCenter\CheckIn\Infrastructure\Persistence\PdoCheckInReadModel;
use TynkaControlCenter\CheckIn\Infrastructure\Persistence\PdoCheckInRepository;
use TynkaControlCenter\Handler\Application\Query\GetTopHandlersHandler;
use TynkaControlCenter\Infrastructure\Templating\PhpTemplateEngine;
use TynkaControlCenter\Common\Domain\Translator;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$appConfig = new AppConfig(
    appUrl: $_ENV["APP_URL"] ?? "http://localhost:80",
    appVersion: "0.1.9",
    dbDriver: $_ENV["DB_DRIVER"] ?? "sqlite",
    dbDatabase: __DIR__ . "/../storage/database.sqlite",
);

define("BASE_PATH", dirname(__DIR__));
define("VIEW_PATH", BASE_PATH . "/resources/views/");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $routeCollector, ): void {
    $routeCollector->addRoute("POST", "/checkin", "check-in.submit");
    $routeCollector->addRoute("GET", "/", "check-ins.index");
    $routeCollector->addRoute("GET", "/checkin/{id}/edit", "check-ins.edit");
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER["REQUEST_METHOD"];
$uri = $_SERVER["REQUEST_URI"];

// Strip query string (?foo=bar) and decode URI
if (false !== ($pos = strpos($uri, "?"))) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

$pdo = new PDO("{$appConfig->dbDriver}:{$appConfig->dbDatabase}");

$inMemoryHandlerRepository = new InMemoryHandlerRepository();

$checkInRepository = new PdoCheckInRepository(
    $pdo,
    "checkins",
);
$fileTranslator = new FileTranslator(
    __DIR__ . '/../resources/lang',
    $appConfig->locale,
    "en"
);
$recordCheckInHandler = new RecordCheckInHandler($checkInRepository);
$getCheckInHandler = new GetCheckInHandler($checkInRepository);

$getAllHandlersHandler = new GetAllHandlersHandler($inMemoryHandlerRepository);

$getAllHandlers = $getAllHandlersHandler->handle();

$inMemoryCheckInOptionRepository = new \TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInOptionRepository();
$inMemoryCheckInOptionCategoryRepository = new \TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInOptionCategoryRepository();

$getAllCheckInOptionsHandler = new GetAllCheckInOptionsHandler(
    checkInOptionRepository: $inMemoryCheckInOptionRepository,
    checkInOptionCategoryRepository: $inMemoryCheckInOptionCategoryRepository,
);

$checkInReadModel = new PdoCheckInReadModel($pdo, "checkins");

$getAllCheckInsHandler = new GetAllCheckInsHandler(
    readModel: $checkInReadModel,
    handlerRepository: $inMemoryHandlerRepository,
    checkInOptionRepository: $inMemoryCheckInOptionRepository,
    checkInOptionCategoryRepository: $inMemoryCheckInOptionCategoryRepository,
);

$getTopHandlersHandler = new GetTopHandlersHandler(
    readModel: $checkInReadModel,
    handlerRepository: $inMemoryHandlerRepository,
);

$checkInController = new CheckInController(
    recordCheckInHandler: $recordCheckInHandler,
    translator: $fileTranslator,
    appConfig: $appConfig,
    viewRenderer: new PhpTemplateEngine(
        templatePath: BASE_PATH,
        appConfig: $appConfig
    ),
    getCheckInHandler: $getCheckInHandler,
    getAllHandlersHandler: $getAllHandlersHandler,
    getAllCheckInOptionsHandler: $getAllCheckInOptionsHandler,
    getTopHandlersHandler: $getTopHandlersHandler,
    getAllCheckInsHandler: $getAllCheckInsHandler,
);

$accessToken = $_GET["access_token"] ?? null;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($accessToken && $accessToken === $_ENV["ACCESS_TOKEN"]) {
    $_SESSION["access_granted"] = true;
}

$accessGranted = $_SESSION["access_granted"] ?? false;

if (!$accessGranted) {
    http_response_code(403);
    echo "Access denied. Please provide a valid access token.";
    exit();
}

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
            "check-in.submit" => $checkInController->handleCheckInSubmission(),
            "check-ins.index" => $checkInController->index(),
            "check-ins.edit" => $checkInController->edit($vars),
            default => throw new RuntimeException(
                "Unknown route handler: $handler",
            ),
        };

        break;
}

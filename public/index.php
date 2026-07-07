<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use TynkaControlCenter\CheckIn\Presentation\CheckInController;

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/resources/views/');

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Access guard ----------------------------------------------------------
$accessToken = $_GET['access_token'] ?? null;

if ($accessToken && $accessToken === ($_ENV['ACCESS_TOKEN'] ?? null)) {
    $_SESSION['access_granted'] = true;
}

if (!($_SESSION['access_granted'] ?? false)) {
    http_response_code(403);
    echo 'Access denied. Please provide a valid access token.';
    exit();
}

// --- Container -------------------------------------------------------------
$container = (new ContainerBuilder())
    ->addDefinitions(BASE_PATH . '/config/container.php')
    ->build();

// --- Routing ---------------------------------------------------------------
$dispatcher = FastRoute\simpleDispatcher(
    require BASE_PATH . '/config/routes.php',
);

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode the URI.
if (false !== ($pos = strpos($uri, '?'))) {
    $uri = substr($uri, 0, $pos);
}

$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller = $container->get(CheckInController::class);
        $vars = $routeInfo[2];

        match ($routeInfo[1]) {
            'check-in.submit' => $controller->handleCheckInSubmission(),
            'check-ins.index' => $controller->index(),
            'check-ins.edit' => $controller->edit($vars),
            default => throw new RuntimeException(
                "Unknown route handler: {$routeInfo[1]}",
            ),
        };

        break;
}

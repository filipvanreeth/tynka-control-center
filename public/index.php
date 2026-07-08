<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use TynkaControlCenter\CheckIn\Presentation\CheckInController;
use TynkaControlCenter\Infrastructure\Http\ResponseEmitter;
use TynkaControlCenter\Infrastructure\Http\Session;

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/resources/views/');

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$container = (new ContainerBuilder())
    ->addDefinitions(BASE_PATH . '/config/container.php')
    ->build();

// --- De onzuivere schil: superglobals één keer inlezen tot een PSR-7 request ---
$psr17Factory = new Psr17Factory();
$request = (new ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
))->fromGlobals();

$emitter = $container->get(ResponseEmitter::class);
$session = $container->get(Session::class);

// --- Access guard: levert een Response i.p.v. echo + exit ---
$accessToken = $request->getQueryParams()['access_token'] ?? null;

if ($accessToken && $accessToken === ($_ENV['ACCESS_TOKEN'] ?? null)) {
    $session->set('access_granted', true);
}

if (!$session->get('access_granted', false)) {
    $emitter->emit(new Response(403, [], 'Access denied. Please provide a valid access token.'));

    return;
}

// --- Routing ---
$dispatcher = FastRoute\simpleDispatcher(
    require BASE_PATH . '/config/routes.php',
);

$routeInfo = $dispatcher->dispatch(
    $request->getMethod(),
    rawurldecode($request->getUri()->getPath()),
);

if (FastRoute\Dispatcher::FOUND === $routeInfo[0]) {
    $controller = $container->get(CheckInController::class);

    // Route-parameters ({id}) reizen mee als request-attributen (PSR-idioom).
    foreach ($routeInfo[2] as $name => $value) {
        $request = $request->withAttribute($name, $value);
    }

    $response = match ($routeInfo[1]) {
        'check-in.submit' => $controller->handleCheckInSubmission($request),
        'check-ins.index' => $controller->index($request),
        'check-ins.edit' => $controller->edit($request),
        default => throw new RuntimeException("Unknown route handler: {$routeInfo[1]}"),
    };
} elseif (FastRoute\Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
    $response = new Response(405);
} else {
    $response = new Response(404);
}

$emitter->emit($response);

<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use TynkaControlCenter\Infrastructure\Http\ResponseEmitter;
use TynkaControlCenter\Infrastructure\Http\Session;

/*
 * Web-entrypoint. Dun: gedeelde bootstrap → sessie (web-specifiek) → één keer de
 * globals inlezen → guard → routing → emit.
 */

/** @var ContainerInterface $container */
$container = require dirname(__DIR__) . '/config/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    // De route draagt de handler als [controller-klasse, methode]; de container
    // resolvet de klasse (autowired), daarna roepen we de actie aan.
    [$controllerClass, $method] = $routeInfo[1];

    // Route-parameters ({id}) reizen mee als request-attributen (PSR-idioom).
    foreach ($routeInfo[2] as $name => $value) {
        $request = $request->withAttribute($name, $value);
    }

    $controller = $container->get($controllerClass);
    $response = $controller->$method($request);
} elseif (FastRoute\Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
    $response = new Response(405);
} else {
    $response = new Response(404);
}

$emitter->emit($response);

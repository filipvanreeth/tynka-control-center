<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use TynkaControlCenter\Access\Presentation\Http\AuthenticationMiddleware;
use TynkaControlCenter\Access\Presentation\Http\AuthorizationMiddleware;
use TynkaControlCenter\Infrastructure\Http\MiddlewarePipeline;
use TynkaControlCenter\Infrastructure\Http\ResponseEmitter;
use TynkaControlCenter\Infrastructure\Http\RouteDispatcher;

/*
 * Web-entrypoint. Dun: gedeelde bootstrap → sessie (web-specifiek) → globals
 * inlezen → middleware-pijplijn (authenticatie → autorisatie → route-dispatch).
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

// --- Middleware-pijplijn met de route-dispatch als sluitstuk ---
$pipeline = new MiddlewarePipeline(
    [
        $container->get(AuthenticationMiddleware::class),
        $container->get(AuthorizationMiddleware::class),
    ],
    $container->get(RouteDispatcher::class),
);

$container->get(ResponseEmitter::class)->emit($pipeline->handle($request));

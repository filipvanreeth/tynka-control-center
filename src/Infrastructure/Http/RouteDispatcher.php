<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Http;

use FastRoute\Dispatcher;
use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Sluitstuk van de pijplijn: matcht de route, resolvet de controller uit de
 * container en roept de actie aan. Route-parameters reizen mee als attributen.
 */
final class RouteDispatcher implements RequestHandlerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch(
            $request->getMethod(),
            rawurldecode($request->getUri()->getPath()),
        );

        if (Dispatcher::FOUND !== $routeInfo[0]) {
            return new Response(Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0] ? 405 : 404);
        }

        [$controllerClass, $method] = $routeInfo[1];

        foreach ($routeInfo[2] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $this->container->get($controllerClass)->$method($request);
    }
}

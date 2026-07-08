<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 pijplijn: loopt een rij middlewares af en dispatcht daarna naar de
 * finale handler. Elke middleware krijgt "de rest van de rij" als handler en
 * beslist zelf of (en wanneer) hij die aanroept — dat is het onion-model.
 */
final class MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * @param list<MiddlewareInterface> $queue
     */
    public function __construct(
        private readonly array $queue,
        private readonly RequestHandlerInterface $finalHandler,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queue = $this->queue;
        $next = array_shift($queue);

        if ($next === null) {
            return $this->finalHandler->handle($request);
        }

        // Kopie van de resterende rij → de pijplijn blijft herbruikbaar.
        return $next->process($request, new self($queue, $this->finalHandler));
    }
}

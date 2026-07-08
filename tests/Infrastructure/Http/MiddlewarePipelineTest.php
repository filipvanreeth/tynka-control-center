<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Http;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TynkaControlCenter\Infrastructure\Http\MiddlewarePipeline;

final class MiddlewarePipelineTest extends TestCase
{
    public function testFallsThroughToTheFinalHandlerWhenEmpty(): void
    {
        $pipeline = new MiddlewarePipeline([], $this->trailEchoingHandler());

        $response = $pipeline->handle(new ServerRequest('GET', '/'));

        self::assertSame('.final', (string) $response->getBody());
    }

    public function testRunsMiddlewareInOrderBeforeTheFinalHandler(): void
    {
        $pipeline = new MiddlewarePipeline(
            [$this->appendingMiddleware('a'), $this->appendingMiddleware('b')],
            $this->trailEchoingHandler(),
        );

        $response = $pipeline->handle(new ServerRequest('GET', '/'));

        self::assertSame('ab.final', (string) $response->getBody());
    }

    public function testAMiddlewareCanShortCircuitTheRest(): void
    {
        $pipeline = new MiddlewarePipeline(
            [$this->shortCircuitMiddleware('blocked'), $this->appendingMiddleware('never')],
            $this->trailEchoingHandler(),
        );

        $response = $pipeline->handle(new ServerRequest('GET', '/'));

        self::assertSame('blocked', (string) $response->getBody());
    }

    private function appendingMiddleware(string $label): MiddlewareInterface
    {
        return new class ($label) implements MiddlewareInterface {
            public function __construct(private readonly string $label)
            {
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                $trail = (string) $request->getAttribute('trail', '');

                return $handler->handle($request->withAttribute('trail', $trail . $this->label));
            }
        };
    }

    private function shortCircuitMiddleware(string $body): MiddlewareInterface
    {
        return new class ($body) implements MiddlewareInterface {
            public function __construct(private readonly string $body)
            {
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                return new Response(200, [], $this->body);
            }
        };
    }

    private function trailEchoingHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response(200, [], (string) $request->getAttribute('trail', '') . '.final');
            }
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Test-dubbel voor het sluitstuk van een middleware-pijplijn: onthoudt of (en
 * met welke request) hij is aangeroepen, zodat tests kunnen asserten dat een
 * middleware doorlaat of kortsluit.
 */
final class SpyRequestHandler implements RequestHandlerInterface
{
    public bool $called = false;

    public ?ServerRequestInterface $received = null;

    public function __construct(
        private readonly ResponseInterface $response = new Response(200),
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->called = true;
        $this->received = $request;

        return $this->response;
    }
}

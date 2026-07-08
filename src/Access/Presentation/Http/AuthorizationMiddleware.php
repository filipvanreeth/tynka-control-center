<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Presentation\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Config\AppConfig;

/**
 * "Mag je hier zijn?" — path-based whitelist: /login is publiek, al de rest
 * vereist een ingelogd account, /admin/* vereist bovendien de admin-rol.
 * Fijnmazige per-route-rollen kunnen later via route-metadata.
 */
final class AuthorizationMiddleware implements MiddlewareInterface
{
    /** @var list<string> */
    private const PUBLIC_PATHS = ['/login'];

    public function __construct(
        private readonly AppConfig $appConfig,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (\in_array($path, self::PUBLIC_PATHS, true)) {
            return $handler->handle($request);
        }

        $account = $request->getAttribute(AuthenticationMiddleware::ATTRIBUTE);

        if (!$account instanceof Account) {
            return new Response(302, ['Location' => $this->appConfig->appUrl . '/login']);
        }

        if (str_starts_with($path, '/admin') && !$account->isAdmin()) {
            return new Response(403, [], 'Forbidden');
        }

        return $handler->handle($request);
    }
}

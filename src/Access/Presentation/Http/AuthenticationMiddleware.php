<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Presentation\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TynkaControlCenter\Access\Domain\AccountId;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Infrastructure\Http\Session;

/**
 * "Wie ben je?" — laadt het ingelogde account uit de sessie en hangt het als
 * request-attribuut. Beslist niets over toegang; dat doet de autorisatie-laag.
 */
final class AuthenticationMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'currentAccount';

    public function __construct(
        private readonly Session $session,
        private readonly AccountRepository $accounts,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $accountId = $this->session->get('account_id');

        $account = \is_string($accountId)
            ? $this->accounts->byId(AccountId::fromString($accountId))
            : null;

        return $handler->handle($request->withAttribute(self::ATTRIBUTE, $account));
    }
}

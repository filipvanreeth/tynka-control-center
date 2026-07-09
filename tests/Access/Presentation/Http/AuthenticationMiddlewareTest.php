<?php

declare(strict_types=1);

namespace App\Tests\Access\Presentation\Http;

use App\Tests\Support\InMemoryAccountRepository;
use App\Tests\Support\InMemorySession;
use App\Tests\Support\SpyRequestHandler;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Access\Presentation\Http\AuthenticationMiddleware;
use TynkaControlCenter\User\Domain\UserId;

final class AuthenticationMiddlewareTest extends TestCase
{
    public function testAttachesTheLoggedInAccountToTheRequest(): void
    {
        $accounts = new InMemoryAccountRepository();
        $account = $this->anAccount();
        $accounts->save($account);

        $session = new InMemorySession();
        $session->set('account_id', $account->id()->toString());

        $received = $this->capturedRequestThrough($session, $accounts);

        $current = $received->getAttribute(AuthenticationMiddleware::ATTRIBUTE);
        self::assertInstanceOf(Account::class, $current);
        self::assertTrue($current->id()->equals($account->id()));
    }

    public function testAttachesNullWhenNotLoggedIn(): void
    {
        $received = $this->capturedRequestThrough(new InMemorySession(), new InMemoryAccountRepository());

        self::assertNull($received->getAttribute(AuthenticationMiddleware::ATTRIBUTE));
    }

    public function testAttachesNullForAnUnknownAccountId(): void
    {
        $session = new InMemorySession();
        $session->set('account_id', 'does-not-exist');

        $received = $this->capturedRequestThrough($session, new InMemoryAccountRepository());

        self::assertNull($received->getAttribute(AuthenticationMiddleware::ATTRIBUTE));
    }

    private function capturedRequestThrough(
        InMemorySession $session,
        InMemoryAccountRepository $accounts,
    ): ServerRequestInterface {
        $spy = new SpyRequestHandler();

        (new AuthenticationMiddleware($session, $accounts))
            ->process(new ServerRequest('GET', '/'), $spy);

        self::assertNotNull($spy->received);

        return $spy->received;
    }

    private function anAccount(): Account
    {
        return Account::register(
            Email::fromString('jan@tynka.be'),
            PasswordHash::fromPlainText('s3cret'),
            Role::Handler,
            UserId::fromString('handler-1'),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Access\Presentation\Http;

use App\Tests\Support\SpyRequestHandler;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Access\Presentation\Http\AuthenticationMiddleware;
use TynkaControlCenter\Access\Presentation\Http\AuthorizationMiddleware;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\User\Domain\UserId;

final class AuthorizationMiddlewareTest extends TestCase
{
    public function testLetsAnyoneReachAPublicPath(): void
    {
        $handler = new SpyRequestHandler();

        $response = $this->authorize('/login', null, $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testRedirectsAnAnonymousUserToLogin(): void
    {
        $handler = new SpyRequestHandler();

        $response = $this->authorize('/', null, $handler);

        self::assertFalse($handler->called);
        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/login', $response->getHeaderLine('Location'));
    }

    public function testLetsAnAuthenticatedUserThrough(): void
    {
        $handler = new SpyRequestHandler();

        $response = $this->authorize('/', $this->account(Role::Handler), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    public function testForbidsANonAdminOnAdminPaths(): void
    {
        $handler = new SpyRequestHandler();

        $response = $this->authorize('/admin/activities', $this->account(Role::Handler), $handler);

        self::assertFalse($handler->called);
        self::assertSame(403, $response->getStatusCode());
    }

    public function testLetsAnAdminOnAdminPaths(): void
    {
        $handler = new SpyRequestHandler();

        $response = $this->authorize('/admin/activities', $this->account(Role::Admin), $handler);

        self::assertTrue($handler->called);
        self::assertSame(200, $response->getStatusCode());
    }

    private function authorize(string $path, ?Account $account, SpyRequestHandler $handler): ResponseInterface
    {
        $request = (new ServerRequest('GET', $path))
            ->withAttribute(AuthenticationMiddleware::ATTRIBUTE, $account);

        return (new AuthorizationMiddleware($this->appConfig()))->process($request, $handler);
    }

    private function account(Role $role): Account
    {
        return Account::register(
            Email::fromString('jan@tynka.be'),
            PasswordHash::fromPlainText('s3cret'),
            $role,
            UserId::fromString('handler-1'),
        );
    }

    private function appConfig(): AppConfig
    {
        return new AppConfig(
            appUrl: 'https://app.test',
            appVersion: '0',
            dbDriver: 'sqlite',
            dbDatabase: ':memory:',
        );
    }
}

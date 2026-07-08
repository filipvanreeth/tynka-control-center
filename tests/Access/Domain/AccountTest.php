<?php

declare(strict_types=1);

namespace App\Tests\Access\Domain;

use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Handler\Domain\HandlerId;

final class AccountTest extends TestCase
{
    public function testVerifiesItsOwnPassword(): void
    {
        $account = $this->register(Role::Handler, 's3cret');

        self::assertTrue($account->verifyPassword('s3cret'));
        self::assertFalse($account->verifyPassword('wrong'));
    }

    public function testHandlerRoleIsNotAdmin(): void
    {
        self::assertFalse($this->register(Role::Handler)->isAdmin());
    }

    public function testAdminRoleIsAdmin(): void
    {
        self::assertTrue($this->register(Role::Admin)->isAdmin());
    }

    public function testLinksToAHandlerById(): void
    {
        $account = $this->register(Role::Handler);

        self::assertSame('handler-1', $account->handlerId()->toString());
    }

    private function register(Role $role, string $password = 's3cret'): Account
    {
        return Account::register(
            Email::fromString('jan@tynka.be'),
            PasswordHash::fromPlainText($password),
            $role,
            HandlerId::fromString('handler-1'),
        );
    }
}

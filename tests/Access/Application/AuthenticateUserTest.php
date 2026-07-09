<?php

declare(strict_types=1);

namespace App\Tests\Access\Application;

use App\Tests\Support\InMemoryAccountRepository;
use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Access\Application\AuthenticateUser;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\User\Domain\UserId;

final class AuthenticateUserTest extends TestCase
{
    public function testReturnsTheAccountForCorrectCredentials(): void
    {
        $result = $this->authenticateWith('jan@tynka.be', 's3cret');

        self::assertNotNull($result);
        self::assertSame('handler-1', $result->userId()->toString());
    }

    public function testRejectsAWrongPassword(): void
    {
        self::assertNull($this->authenticateWith('jan@tynka.be', 'wrong'));
    }

    public function testRejectsAnUnknownEmail(): void
    {
        self::assertNull($this->authenticateWith('nobody@tynka.be', 's3cret'));
    }

    public function testRejectsAMalformedEmailWithoutThrowing(): void
    {
        self::assertNull($this->authenticateWith('not-an-email', 's3cret'));
    }

    private function authenticateWith(string $email, string $plainPassword): ?Account
    {
        $accounts = new InMemoryAccountRepository();
        $accounts->save(Account::register(
            Email::fromString('jan@tynka.be'),
            PasswordHash::fromPlainText('s3cret'),
            Role::Handler,
            UserId::fromString('handler-1'),
        ));

        return (new AuthenticateUser($accounts))($email, $plainPassword);
    }
}

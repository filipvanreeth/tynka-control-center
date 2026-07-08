<?php

declare(strict_types=1);

namespace App\Tests\Access\Infrastructure\Persistence;

use PDO;
use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Access\Infrastructure\Persistence\PdoAccountRepository;
use TynkaControlCenter\Handler\Domain\HandlerId;

/**
 * Roundtrip-test tegen een in-memory SQLite: bewijst dat de adapter het domein
 * ↔ de kolommen correct vertaalt (echte SQL, geen mock).
 */
final class PdoAccountRepositoryTest extends TestCase
{
    public function testSavesAndFindsByEmail(): void
    {
        $repository = $this->repositoryWithSchema();
        $account = $this->register(Role::Admin);
        $repository->save($account);

        $found = $repository->byEmail(Email::fromString('jan@tynka.be'));

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($account->id()));
        self::assertTrue($found->verifyPassword('s3cret'));
        self::assertTrue($found->isAdmin());
        self::assertSame('handler-1', $found->handlerId()->toString());
    }

    public function testSavesAndFindsById(): void
    {
        $repository = $this->repositoryWithSchema();
        $account = $this->register(Role::Handler);
        $repository->save($account);

        $found = $repository->byId($account->id());

        self::assertNotNull($found);
        self::assertTrue($found->email()->equals(Email::fromString('jan@tynka.be')));
    }

    public function testReturnsNullForUnknownEmail(): void
    {
        $repository = $this->repositoryWithSchema();

        self::assertNull($repository->byEmail(Email::fromString('nobody@tynka.be')));
    }

    private function register(Role $role): Account
    {
        return Account::register(
            Email::fromString('jan@tynka.be'),
            PasswordHash::fromPlainText('s3cret'),
            $role,
            HandlerId::fromString('handler-1'),
        );
    }

    private function repositoryWithSchema(): PdoAccountRepository
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec(
            'CREATE TABLE accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                uuid TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL,
                handler_id TEXT NOT NULL
            )'
        );

        return new PdoAccountRepository($pdo, 'accounts');
    }
}

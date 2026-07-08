<?php

declare(strict_types=1);

namespace App\Tests\Support;

use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\AccountId;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Domain\Email;

/**
 * In-memory adapter voor de {@see AccountRepository}-poort. Laat de authenticatie-
 * keten (Stap B) en de middleware (Stap D) zonder database getest worden.
 */
final class InMemoryAccountRepository implements AccountRepository
{
    /** @var array<string, Account> */
    private array $accounts = [];

    public function save(Account $account): void
    {
        $this->accounts[$account->id()->toString()] = $account;
    }

    public function byId(AccountId $id): ?Account
    {
        return $this->accounts[$id->toString()] ?? null;
    }

    public function byEmail(Email $email): ?Account
    {
        foreach ($this->accounts as $account) {
            if ($account->email()->equals($email)) {
                return $account;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Domain;

interface AccountRepository
{
    public function save(Account $account): void;

    public function byId(AccountId $id): ?Account;

    public function byEmail(Email $email): ?Account;
}

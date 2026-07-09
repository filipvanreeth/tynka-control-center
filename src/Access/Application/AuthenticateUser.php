<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Application;

use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\InvalidEmail;

final class AuthenticateUser
{
    public function __construct(
        private readonly AccountRepository $accounts,
    ) {
    }

    public function __invoke(string $email, string $plainPassword): ?Account
    {
        try {
            $account = $this->accounts->byEmail(Email::fromString($email));
        } catch (InvalidEmail) {
            return null;
        }

        if ($account === null || !$account->verifyPassword($plainPassword)) {
            return null;
        }

        return $account;
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Application;

use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\AccountRepository;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\EmailAlreadyRegistered;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Handler\Domain\HandlerId;

/**
 * Applicatie-geval dat één account met een rol aanmaakt. Bewust in dezelfde stijl
 * als {@see AuthenticateUser}: een invokable service, geen Command/Handler-bus —
 * Access is een generiek subdomein dat leen wordt gehouden.
 *
 * Enige plek waar de Access-invarianten van accountcreatie samenkomen (uniek
 * e-mailadres, hashing via het domein). De handler-bestaat-check is cross-context
 * orkestratie en blijft bij de aanroeper (het CLI-entrypoint) — deze service raakt
 * enkel de gedeelde {@see HandlerId}-identiteit, niet de Handler-repository.
 */
final class RegisterAccount
{
    public function __construct(
        private readonly AccountRepository $accounts,
    ) {
    }

    public function __invoke(
        string $email,
        string $plainPassword,
        Role $role,
        HandlerId $handlerId,
    ): Account {
        $emailVo = Email::fromString($email);

        if ($this->accounts->byEmail($emailVo) !== null) {
            throw EmailAlreadyRegistered::withEmail($emailVo);
        }

        $account = Account::register(
            $emailVo,
            PasswordHash::fromPlainText($plainPassword),
            $role,
            $handlerId,
        );

        $this->accounts->save($account);

        return $account;
    }
}

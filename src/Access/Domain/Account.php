<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Domain;

use TynkaControlCenter\Handler\Domain\HandlerId;

/**
 * Een account in de Access-context: iemand die kan inloggen. Bewust géén
 * `Handler` — het linkt via {@see HandlerId} naar de Handler-context (contexten
 * delen geen entities, enkel een id). Rol zit hier, niet op de Handler.
 *
 * Auth is een generiek subdomein, dus dit is een dunne entity zonder Domain
 * Events of AggregateRoot-machinerie: net genoeg om te authenticeren en de rol +
 * handler-link te dragen.
 */
final class Account
{
    private function __construct(
        private readonly AccountId $id,
        private readonly Email $email,
        private readonly PasswordHash $passwordHash,
        private readonly Role $role,
        private readonly HandlerId $handlerId,
    ) {
    }

    public static function register(
        Email $email,
        PasswordHash $passwordHash,
        Role $role,
        HandlerId $handlerId,
    ): self {
        return new self(
            id: AccountId::generate(),
            email: $email,
            passwordHash: $passwordHash,
            role: $role,
            handlerId: $handlerId,
        );
    }

    public static function reconstitute(
        AccountId $id,
        Email $email,
        PasswordHash $passwordHash,
        Role $role,
        HandlerId $handlerId,
    ): self {
        return new self(
            id: $id,
            email: $email,
            passwordHash: $passwordHash,
            role: $role,
            handlerId: $handlerId,
        );
    }

    public function verifyPassword(string $plainText): bool
    {
        return $this->passwordHash->verify($plainText);
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    public function id(): AccountId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function handlerId(): HandlerId
    {
        return $this->handlerId;
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Domain;

use DomainException;

final class EmailAlreadyRegistered extends DomainException
{
    public static function withEmail(Email $email): self
    {
        return new self("An account for '{$email->toString()}' already exists.");
    }
}

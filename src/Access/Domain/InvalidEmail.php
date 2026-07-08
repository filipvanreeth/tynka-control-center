<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Domain;

use DomainException;

final class InvalidEmail extends DomainException
{
    public static function invalidFormat(string $value): self
    {
        return new self("'{$value}' is not a valid email address.");
    }
}

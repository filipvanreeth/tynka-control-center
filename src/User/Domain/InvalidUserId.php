<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Domain;

use DomainException;

final class InvalidUserId extends DomainException
{
    public static function emptyValue(): self
    {
        return new self("User id cannot be empty");
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Domain;

use DomainException;

final class InvalidHandlerId extends DomainException
{
    public static function emptyValue(): self
    {
        return new self("Handler id cannot be empty");
    }
}

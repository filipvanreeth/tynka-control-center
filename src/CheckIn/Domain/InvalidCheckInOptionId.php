<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DomainException;

final class InvalidCheckInOptionId extends DomainException
{
    public static function emptyValue(): self
    {
        return new self("Check-in option id cannot be empty");
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DomainException;

final class InvalidCheckInActivityId extends DomainException
{
    public static function emptyValue(): self
    {
        return new self("Check-in activity id cannot be empty");
    }
}

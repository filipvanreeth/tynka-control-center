<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DomainException;

final class InvalidCheckIn extends DomainException
{
    public static function withoutActivities(): self
    {
        return new self('A check-in must record at least one activity.');
    }
}

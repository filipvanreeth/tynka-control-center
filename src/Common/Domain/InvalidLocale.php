<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

use DomainException;
use function sprintf;

final class InvalidLocale extends DomainException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf("%s is not a valid locale", $value));
    }
}
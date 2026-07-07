<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DomainException;

final class InvalidCheckInActivityCategoryId extends DomainException
{
    public static function emptyValue(): self
    {
        return new self("Check-in activity category id cannot be empty");
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

use DomainException;

final class InvalidAvatar extends DomainException
{
    public static function emptyPath(): self
    {
        return new self("Avatar path cannot be empty");
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

use DomainException;
use function sprintf;

final class InvalidSlug extends DomainException
{
    public static function emptyValue(): self
    {
        return new self("Slug cannot be empty");
    }

    public static function invalidCharacters(string $value): self
    {
        return new self(sprintf(
            "%s may only contain letters, numbers, underscores and dashes",
            $value
        ));
    }
}

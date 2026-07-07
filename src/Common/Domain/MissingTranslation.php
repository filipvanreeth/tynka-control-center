<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

use DomainException;
use function sprintf;

final class MissingTranslation extends DomainException
{
    public static function forLocale(Locale $locale): self
    {
        return new self(sprintf("No translation available for locale %s", $locale->value()));
    }
}

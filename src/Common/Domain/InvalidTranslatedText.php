<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

use DomainException;
use function sprintf;

final class InvalidTranslatedText extends DomainException
{
    public static function emptyTranslations(): self
    {
        return new self("Translations cannot be empty");
    }

    public static function emptyText(string $locale): self
    {
        return new self(sprintf("Translation text for locale %s cannot be empty", $locale));
    }
}

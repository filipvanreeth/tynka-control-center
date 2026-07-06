<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategory;
use TynkaControlCenter\Common\Domain\Locale;

final readonly class CheckInOptionCategoryData
{
    public function __construct(
        public string $title,
        public string $slug,
    ) {
    }

    public static function fromDomain(
        CheckInOptionCategory $domain,
        Locale $locale,
    ): self {
        return new self(
            $domain->title()->forLocale($locale),
            $domain->slug()->value()
        );
    }
}
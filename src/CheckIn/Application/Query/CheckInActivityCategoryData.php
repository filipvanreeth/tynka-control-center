<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategory;
use TynkaControlCenter\Common\Domain\Locale;

final readonly class CheckInActivityCategoryData
{
    public function __construct(
        public string $title,
        public string $id,
    ) {
    }

    public static function fromDomain(
        CheckInActivityCategory $domain,
        Locale $locale,
    ): self {
        return new self(
            $domain->title()->forLocale($locale),
            $domain->id()->toString()
        );
    }
}
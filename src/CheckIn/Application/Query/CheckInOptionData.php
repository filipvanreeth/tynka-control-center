<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInOption;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategory;
use TynkaControlCenter\Common\Domain\Locale;

final readonly class CheckInOptionData
{
    public function __construct(
        public string $title,
        public string $slug,
        public CheckInOptionCategoryData $category,
    ) {
    }

    public static function fromDomain(
        CheckInOption $option,
        CheckInOptionCategory $category,
        Locale $locale,
    ): self {
        return new self(
            title: $option->title()->forLocale($locale),
            slug: $option->id()->toString(),
            category: CheckInOptionCategoryData::fromDomain(
                domain: $category,
                locale: $locale,
            ),
        );
    }
}
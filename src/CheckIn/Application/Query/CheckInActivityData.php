<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInActivity;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategory;
use TynkaControlCenter\Common\Domain\Locale;

final readonly class CheckInActivityData
{
    public function __construct(
        public string $title,
        public string $id,
        public CheckInActivityCategoryData $category,
    ) {
    }

    public static function fromDomain(
        CheckInActivity $activity,
        CheckInActivityCategory $category,
        Locale $locale,
    ): self {
        return new self(
            title: $activity->title()->forLocale($locale),
            id: $activity->id()->toString(),
            category: CheckInActivityCategoryData::fromDomain(
                domain: $category,
                locale: $locale,
            ),
        );
    }
}
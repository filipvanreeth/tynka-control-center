<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInActivity;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategoryRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\Common\Domain\Locale;

final class GetAllCheckInActivitiesHandler
{
    public function __construct(
        private CheckInActivityRepository $checkInActivityRepository,
        private CheckInActivityCategoryRepository $checkInActivityCategoryRepository,
    ) {
    }

    /**
     * @return list<CheckInActivityData>
     */
    public function handle(GetAllCheckInActivitiesQuery $query): array
    {
        $allCheckInActivities = $this->checkInActivityRepository->findAll();
        $locale = new Locale($query->locale);

        return array_map(
            function (CheckInActivity $activity) use ($locale): CheckInActivityData {
                $category = $this->checkInActivityCategoryRepository->byId(
                    $activity->categoryId()
                );

                if ($category === null) {
                    throw new \InvalidArgumentException('Category not found');
                }

                return CheckInActivityData::fromDomain(
                    activity: $activity,
                    category: $category,
                    locale: $locale
                );
            },
            $allCheckInActivities
        );
    }
}

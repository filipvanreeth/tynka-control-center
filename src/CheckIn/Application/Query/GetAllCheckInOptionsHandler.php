<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInOption;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;
use TynkaControlCenter\Common\Domain\Locale;

final class GetAllCheckInOptionsHandler
{
    public function __construct(
        private CheckInOptionRepository $checkInOptionRepository,
        private CheckInOptionCategoryRepository $checkInOptionCategoryRepository,
    ) {
    }

    /**
     * @return list<CheckInOptionData>
     */
    public function handle(GetAllCheckInOptionsQuery $query): array
    {
        $allCheckInOptions = $this->checkInOptionRepository->findAll();
        $locale = new Locale($query->locale);

        return array_map(
            function (CheckInOption $option) use ($locale): CheckInOptionData {
                $category = $this->checkInOptionCategoryRepository->byId(
                    $option->categoryId()
                );

                if ($category === null) {
                    throw new \InvalidArgumentException('Category not found');
                }

                return CheckInOptionData::fromDomain(
                    option: $option,
                    category: $category,
                    locale: $locale
                );
            },
            $allCheckInOptions
        );
    }
}

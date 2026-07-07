<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure;

use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategory;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategoryId;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategoryRepository;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class InMemoryCheckInActivityCategoryRepository implements CheckInActivityCategoryRepository
{
    /**
     * @return list<CheckInActivityCategory>
     */
    public function findAll(): array
    {
        return [
            CheckInActivityCategory::reconstitute(
                id: CheckInActivityCategoryId::fromString('walking'),
                title: new TranslatedText(['en' => 'Walking', 'nl' => 'Wandelen']),
            ),
            CheckInActivityCategory::reconstitute(
                id: CheckInActivityCategoryId::fromString('food'),
                title: new TranslatedText(['en' => 'Food', 'nl' => 'Voeding']),
            ),
        ];
    }

    public function byId(CheckInActivityCategoryId $id): ?CheckInActivityCategory
    {
        foreach ($this->findAll() as $category) {
            if ($category->id()->equals($id)) {
                return $category;
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure;

use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategory;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryId;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryRepository;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class InMemoryCheckInOptionCategoryRepository implements CheckInOptionCategoryRepository
{
    /**
     * @return list<CheckInOptionCategory>
     */
    public function findAll(): array
    {
        return [
            CheckInOptionCategory::reconstitute(
                id: CheckInOptionCategoryId::fromString('walking'),
                title: new TranslatedText(['en' => 'Walking', 'nl' => 'Wandelen']),
            ),
            CheckInOptionCategory::reconstitute(
                id: CheckInOptionCategoryId::fromString('food'),
                title: new TranslatedText(['en' => 'Food', 'nl' => 'Voeding']),
            ),
        ];
    }

    public function byId(CheckInOptionCategoryId $id): ?CheckInOptionCategory
    {
        foreach ($this->findAll() as $category) {
            if ($category->id()->equals($id)) {
                return $category;
            }
        }

        return null;
    }
}

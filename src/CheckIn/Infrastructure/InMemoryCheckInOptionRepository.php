<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure;

use TynkaControlCenter\CheckIn\Domain\CheckInOption;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryId;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionId;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class InMemoryCheckInOptionRepository implements CheckInOptionRepository
{
    /**
     * @return list<CheckInOption>
     */
    public function findAll(): array
    {
        return [
            CheckInOption::reconstitute(
                id: CheckInOptionId::fromString('peed'),
                title: new TranslatedText(['en' => 'Peed', 'nl' => 'Geplast']),
                categoryId: CheckInOptionCategoryId::fromString('walking'),
            ),
            CheckInOption::reconstitute(
                id: CheckInOptionId::fromString('pooped'),
                title: new TranslatedText(['en' => 'Pooped', 'nl' => 'Gepoept']),
                categoryId: CheckInOptionCategoryId::fromString('walking'),
            ),
            CheckInOption::reconstitute(
                id: CheckInOptionId::fromString('food'),
                title: new TranslatedText(['en' => 'Food', 'nl' => 'Voeding']),
                categoryId: CheckInOptionCategoryId::fromString('food'),
            ),
            CheckInOption::reconstitute(
                id: CheckInOptionId::fromString('snack'),
                title: new TranslatedText(['en' => 'Snack', 'nl' => 'Snack']),
                categoryId: CheckInOptionCategoryId::fromString('food'),
            ),
        ];
    }

    public function byId(CheckInOptionId $id): ?CheckInOption
    {
        foreach ($this->findAll() as $checkInOption) {
            if ($checkInOption->id()->equals($id)) {
                return $checkInOption;
            }
        }

        return null;
    }
}

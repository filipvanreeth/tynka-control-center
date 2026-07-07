<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure;

use TynkaControlCenter\CheckIn\Domain\CheckInActivity;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategoryId;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityId;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class InMemoryCheckInActivityRepository implements CheckInActivityRepository
{
    /**
     * @return list<CheckInActivity>
     */
    public function findAll(): array
    {
        return [
            CheckInActivity::reconstitute(
                id: CheckInActivityId::fromString('peed'),
                title: new TranslatedText(['en' => 'Peed', 'nl' => 'Geplast']),
                categoryId: CheckInActivityCategoryId::fromString('walking'),
            ),
            CheckInActivity::reconstitute(
                id: CheckInActivityId::fromString('pooped'),
                title: new TranslatedText(['en' => 'Pooped', 'nl' => 'Gepoept']),
                categoryId: CheckInActivityCategoryId::fromString('walking'),
            ),
            CheckInActivity::reconstitute(
                id: CheckInActivityId::fromString('food'),
                title: new TranslatedText(['en' => 'Food', 'nl' => 'Voeding']),
                categoryId: CheckInActivityCategoryId::fromString('food'),
            ),
            CheckInActivity::reconstitute(
                id: CheckInActivityId::fromString('snack'),
                title: new TranslatedText(['en' => 'Snack', 'nl' => 'Snack']),
                categoryId: CheckInActivityCategoryId::fromString('food'),
            ),
        ];
    }

    public function byId(CheckInActivityId $id): ?CheckInActivity
    {
        foreach ($this->findAll() as $checkInActivity) {
            if ($checkInActivity->id()->equals($id)) {
                return $checkInActivity;
            }
        }

        return null;
    }
}

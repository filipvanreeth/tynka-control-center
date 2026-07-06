<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure;

use TynkaControlCenter\CheckIn\Domain\CheckInOption;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategory;
use TynkaControlCenter\Common\Domain\title;
use TynkaControlCenter\Common\Domain\Locale;
use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class InMemoryCheckInOptionRepository implements CheckInOptionRepository
{
    /**
     * @return list<CheckInOption>
     */
    public function findAll(): array
    {
        return [
            new CheckInOption(
                title: new TranslatedText(
                    [
                        'en' => 'Peed',
                        'nl' => 'Geplast'
                    ]
                ),
                slug: new Slug('peed'),
                categoryId: new Slug('walking')
            ),
            new CheckInOption(
                title: new TranslatedText(
                    [
                        'en' => 'Pooped',
                        'nl' => 'Gepoept'
                    ]
                ),
                slug: new Slug('pooped'),
                categoryId: new Slug('walking')
            ),
            new CheckInOption(
                title: new TranslatedText(
                    [
                        'en' => 'Food',
                        'nl' => 'Voeding'
                    ]
                ),
                slug: new Slug('food'),
                categoryId: new Slug('food')
            ),
            new CheckInOption(
                title: new TranslatedText(
                    [
                        'en' => 'Snack',
                        'nl' => 'Snack'
                    ]
                ),
                slug: new Slug('snack'),
                categoryId: new Slug('food')
            ),
        ];
    }

    public function findBySlug(Slug $slug): ?CheckInOption
    {
        foreach ($this->findAll() as $checkInOption) {
            if ($checkInOption->slug()->value() === $slug->value()) {
                return $checkInOption;
            }
        }

        return null;
    }
}

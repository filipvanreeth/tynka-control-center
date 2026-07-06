<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure;

use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategory;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryRepository;
use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Common\Domain\TranslatedText;

final class InMemoryCheckInOptionCategoryRepository implements CheckInOptionCategoryRepository
{
    /**
     * @return list<CheckInOptionCategory>
     */
    public function findAll(): array
    {
        return [
            new CheckInOptionCategory(
                title: new TranslatedText(
                    translations: [
                        'en' => 'Walking',
                        'nl' => 'Wandelen',
                    ]
                ),
                slug: new Slug('walking')
            ),
            new CheckInOptionCategory(
                title: new TranslatedText(
                    translations: [
                        'en' => 'Food',
                        'nl' => 'Voeding',
                    ]
                ),
                slug: new Slug('food')
            ),
        ];
    }

    public function findBySlug(Slug $slug): ?CheckInOptionCategory
    {
        foreach ($this->findAll() as $category) {
            if ($category->slug()->value() === $slug->value()) {
                return $category;
            }
        }

        return null;
    }
}

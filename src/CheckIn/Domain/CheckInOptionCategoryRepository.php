<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use TynkaControlCenter\Common\Domain\Slug;

interface CheckInOptionCategoryRepository
{
    /**
     * @return list<CheckInOptionCategory>
     */
    public function findAll(): array;
    public function findBySlug(Slug $slug): ?CheckInOptionCategory;
}

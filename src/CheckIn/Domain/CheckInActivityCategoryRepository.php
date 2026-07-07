<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

interface CheckInActivityCategoryRepository
{
    /**
     * @return list<CheckInActivityCategory>
     */
    public function findAll(): array;

    public function byId(CheckInActivityCategoryId $id): ?CheckInActivityCategory;
}

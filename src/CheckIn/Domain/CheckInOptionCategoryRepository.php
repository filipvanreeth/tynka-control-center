<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

interface CheckInOptionCategoryRepository
{
    /**
     * @return list<CheckInOptionCategory>
     */
    public function findAll(): array;

    public function byId(CheckInOptionCategoryId $id): ?CheckInOptionCategory;
}

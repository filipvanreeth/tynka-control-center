<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

interface CheckInOptionRepository
{
    /**
     * @return list<CheckInOption>
     */
    public function findAll(): array;

    public function byId(CheckInOptionId $id): ?CheckInOption;
}

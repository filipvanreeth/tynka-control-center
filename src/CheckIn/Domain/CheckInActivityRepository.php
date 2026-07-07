<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

interface CheckInActivityRepository
{
    /**
     * @return list<CheckInActivity>
     */
    public function findAll(): array;

    public function byId(CheckInActivityId $id): ?CheckInActivity;
}

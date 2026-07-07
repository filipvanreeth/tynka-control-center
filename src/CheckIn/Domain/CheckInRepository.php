<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

interface CheckInRepository
{
    public function save(CheckIn $checkIn): void;

    public function byId(CheckInId $id): ?CheckIn;
}

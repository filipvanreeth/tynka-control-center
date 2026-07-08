<?php

declare(strict_types=1);

namespace App\Tests\Support;

use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\CheckIn\Domain\CheckInId;
use TynkaControlCenter\CheckIn\Domain\CheckInRepository;

/**
 * In-memory write-side adapter voor de {@see CheckInRepository}-poort. Laat de
 * command-keten (en dus de controller) toe zonder database getest te worden — de
 * DDD-canon beveelt precies zo'n in-memory repository aan voor tests.
 */
final class InMemoryCheckInRepository implements CheckInRepository
{
    /** @var array<string, CheckIn> */
    private array $checkIns = [];

    public function save(CheckIn $checkIn): void
    {
        $this->checkIns[$checkIn->id()->toString()] = $checkIn;
    }

    public function byId(CheckInId $id): ?CheckIn
    {
        return $this->checkIns[$id->toString()] ?? null;
    }

    public function count(): int
    {
        return \count($this->checkIns);
    }
}

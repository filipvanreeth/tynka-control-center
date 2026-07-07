<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInId;
use TynkaControlCenter\CheckIn\Domain\CheckInRepository;

final class GetCheckInHandler
{
    public function __construct(
        private readonly CheckInRepository $checkInRepository,
    ) {
    }

    public function handle(GetCheckInQuery $query): ?CheckInData
    {
        $checkIn = $this->checkInRepository->byId(
            CheckInId::fromString($query->id)
        );

        if ($checkIn === null) {
            return null;
        }

        // PARKED (edit-fix): CheckInData opbouwen via een gecentraliseerde
        // CheckInData::fromDomain(...) — handler (HandlerData) en selectedOptions
        // moeten nog geresolved worden tegen hun repositories. Zie migratiestatus
        // in CLAUDE.md. De edit-route is bewust nog niet functioneel.
        throw new \RuntimeException(
            'GetCheckInHandler: DTO-mapping (CheckInData::fromDomain) nog te implementeren.'
        );
    }
}

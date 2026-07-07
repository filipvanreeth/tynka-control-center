<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure\Persistence;

use DateTimeImmutable;
use PDO;
use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\CheckIn\Domain\CheckInId;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityId;
use TynkaControlCenter\CheckIn\Domain\CheckInRepository;
use TynkaControlCenter\Handler\Domain\HandlerId;

final class PdoCheckInRepository implements CheckInRepository
{
    private const ACTIVITY_COLUMNS = ['peed', 'pooped', 'food', 'snack'];

    public function __construct(
        private readonly PDO $pdo,
        private readonly string $tableName,
    ) {
    }

    public function save(CheckIn $checkIn): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->tableName} (uuid, handler, peed, pooped, food, snack, created_at)
             VALUES (:uuid, :handler, :peed, :pooped, :food, :snack, :created_at)"
        );

        $selectedSlugs = array_map(
            static fn(CheckInActivityId $activity): string => $activity->toString(),
            $checkIn->selectedActivities()
        );

        $executed = $stmt->execute([
            ':uuid' => $checkIn->id()->toString(),
            ':handler' => $checkIn->handlerId()->toString(),
            ':peed' => \in_array('peed', $selectedSlugs, true) ? 1 : 0,
            ':pooped' => \in_array('pooped', $selectedSlugs, true) ? 1 : 0,
            ':food' => \in_array('food', $selectedSlugs, true) ? 1 : 0,
            ':snack' => \in_array('snack', $selectedSlugs, true) ? 1 : 0,
            ':created_at' => $checkIn->createdAt()->format('Y-m-d H:i:s'),
        ]);

        if (!$executed) {
            throw new \RuntimeException(
                'Failed to store check-in: ' . implode(', ', $stmt->errorInfo())
            );
        }
    }

    public function byId(CheckInId $id): ?CheckIn
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tableName} WHERE uuid = :uuid"
        );
        $stmt->execute([':uuid' => $id->toString()]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $selectedActivities = [];
        foreach (self::ACTIVITY_COLUMNS as $activitySlug) {
            if ((bool) $row[$activitySlug]) {
                $selectedActivities[] = CheckInActivityId::fromString($activitySlug);
            }
        }

        return CheckIn::reconstitute(
            id: CheckInId::fromString((string) $row['uuid']),
            handlerId: HandlerId::fromString($row['handler']),
            selectedActivities: $selectedActivities,
            createdAt: new DateTimeImmutable($row['created_at']),
        );
    }
}

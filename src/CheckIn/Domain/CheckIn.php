<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DateTimeImmutable;
use TynkaControlCenter\Common\Domain\AggregateRoot;
use TynkaControlCenter\Handler\Domain\HandlerId;

final class CheckIn extends AggregateRoot
{
    /**
     * @param list<CheckInActivityId> $selectedActivities
     */
    private function __construct(
        private readonly CheckInId $id,
        private readonly HandlerId $handlerId,
        private readonly array $selectedActivities,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @param list<CheckInActivityId> $selectedActivities
     */
    public static function create(
        HandlerId $handlerId,
        array $selectedActivities,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: CheckInId::generate(),
            handlerId: $handlerId,
            selectedActivities: $selectedActivities,
            createdAt: $createdAt,
        );
    }

    /**
     * @param list<CheckInActivityId> $selectedActivities
     */
    public static function reconstitute(
        CheckInId $id,
        HandlerId $handlerId,
        array $selectedActivities,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            handlerId: $handlerId,
            selectedActivities: $selectedActivities,
            createdAt: $createdAt,
        );
    }

    public function id(): CheckInId
    {
        return $this->id;
    }

    public function handlerId(): HandlerId
    {
        return $this->handlerId;
    }

    /**
     * @return list<CheckInActivityId>
     */
    public function selectedActivities(): array
    {
        return $this->selectedActivities;
    }

    public function hasActivity(CheckInActivityId $activityId): bool
    {
        foreach ($this->selectedActivities as $selected) {
            if ($selected->equals($activityId)) {
                return true;
            }
        }

        return false;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

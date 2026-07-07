<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DateTimeImmutable;
use TynkaControlCenter\Common\Domain\AggregateRoot;
use TynkaControlCenter\Handler\Domain\HandlerId;

final class CheckIn extends AggregateRoot
{
    /**
     * @param list<CheckInOptionId> $selectedOptions
     */
    private function __construct(
        private readonly CheckInId $id,
        private readonly HandlerId $handlerId,
        private readonly array $selectedOptions,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @param list<CheckInOptionId> $selectedOptions
     */
    public static function create(
        HandlerId $handlerId,
        array $selectedOptions,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: CheckInId::generate(),
            handlerId: $handlerId,
            selectedOptions: $selectedOptions,
            createdAt: $createdAt,
        );
    }

    /**
     * @param list<CheckInOptionId> $selectedOptions
     */
    public static function reconstitute(
        CheckInId $id,
        HandlerId $handlerId,
        array $selectedOptions,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            handlerId: $handlerId,
            selectedOptions: $selectedOptions,
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
     * @return list<CheckInOptionId>
     */
    public function selectedOptions(): array
    {
        return $this->selectedOptions;
    }

    public function hasOption(CheckInOptionId $optionId): bool
    {
        foreach ($this->selectedOptions as $selected) {
            if ($selected->equals($optionId)) {
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

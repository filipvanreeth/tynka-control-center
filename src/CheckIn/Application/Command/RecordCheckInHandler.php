<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Command;

use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityId;
use TynkaControlCenter\CheckIn\Domain\CheckInRepository;
use TynkaControlCenter\User\Domain\UserId;

final class RecordCheckInHandler
{
    public function __construct(
        private readonly CheckInRepository $checkIns,
    ) {
    }

    public function handle(RecordCheckInCommand $command): void
    {
        $checkIn = CheckIn::create(
            userId: UserId::fromString($command->handler),
            selectedActivities: array_map(
                static fn(string $slug): CheckInActivityId => CheckInActivityId::fromString($slug),
                $command->selectedActivities
            ),
            createdAt: $command->createdAt,
        );

        $this->checkIns->save($checkIn);
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Command;

use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionId;
use TynkaControlCenter\CheckIn\Domain\CheckInRepository;
use TynkaControlCenter\Handler\Domain\HandlerId;

final class RecordCheckInHandler
{
    public function __construct(
        private readonly CheckInRepository $checkIns,
    ) {
    }

    public function handle(RecordCheckInCommand $command): void
    {
        $checkIn = CheckIn::create(
            handlerId: HandlerId::fromString($command->handler),
            selectedOptions: array_map(
                static fn(string $slug): CheckInOptionId => CheckInOptionId::fromString($slug),
                $command->selectedOptions
            ),
            createdAt: $command->createdAt,
        );

        $this->checkIns->save($checkIn);
    }
}

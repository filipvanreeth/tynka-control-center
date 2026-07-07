<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInActivityId;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\Common\Domain\Locale;

final class GetStatisticsForCheckInActivityHandler
{
    public function __construct(
        private readonly CheckInActivityRepository $checkInActivityRepository,
        private readonly CheckInReadModel $readModel,
    ) {
    }

    public function handle(GetStatisticsForCheckInActivityQuery $query): StatisticsCheckInActivityData
    {
        $activity = $this->checkInActivityRepository->byId(
            CheckInActivityId::fromString($query->activity)
        );

        if ($activity === null) {
            throw new \InvalidArgumentException('Invalid check-in activity: ' . $query->activity);
        }

        return new StatisticsCheckInActivityData(
            id: $activity->id()->toString(),
            title: $activity->title()->forLocale(new Locale($query->locale)),
            total: $this->readModel->totalForActivity($query->activity),
        );
    }
}

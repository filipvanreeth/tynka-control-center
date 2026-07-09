<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use DateTimeImmutable;
use TynkaControlCenter\CheckIn\Application\Query\AllCheckInsData;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityCategoryRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityId;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\Common\Domain\Locale;
use TynkaControlCenter\User\Application\Query\UserData;
use TynkaControlCenter\User\Domain\UserId;
use TynkaControlCenter\User\Domain\UserRepository;

final class GetAllCheckInsHandler
{
    public function __construct(
        private readonly CheckInReadModel $readModel,
        private readonly UserRepository $userRepository,
        private CheckInActivityRepository $checkInActivityRepository,
        private CheckInActivityCategoryRepository $checkInActivityCategoryRepository
    ) {
    }

    public function handle(GetAllCheckInsQuery $query): AllCheckInsData
    {
        $rows = $this->readModel->all();

        if (!$rows) {
            return new AllCheckInsData(
                checkIns: [],
                total: 0,
            );
        }

        $locale = new Locale($query->locale);

        $checkIns = array_map(
            fn($row): CheckInData => new CheckInData(
                id: $row['uuid'],
                handler: $this->resolveUser($row['handler']),
                activities: $this->resolveActivities($row, $locale),
                createdAt: (new DateTimeImmutable($row['created_at']))->format('Y-m-d H:i')
            ),
            $rows
        );

        $total = \count($checkIns);

        $results = new AllCheckInsData(
            checkIns: $checkIns,
            total: $total
        );

        return $results;
    }

    private function resolveUser(string $userId): UserData
    {
        $handler = $this->userRepository->byId(UserId::fromString($userId));

        if ($handler === null) {
            return new UserData(
                name: $userId,
                id: $userId,
                avatar: null
            );
        }

        return UserData::fromDomain($handler);
    }

    /**
     * @param array<string, mixed> $row
     * @return CheckInActivityData[]
     */
    private function resolveActivities(array $row, Locale $locale): array
    {
        $selectedActivities = array_keys(
            array_filter([
                'peed' => (bool) $row['peed'],
                'pooped' => (bool) $row['pooped'],
                'food' => (bool) $row['food'],
                'snack' => (bool) $row['snack'],
            ])
        );

        $activities = [];

        foreach ($selectedActivities as $selectedActivity) {
            $activity = $this->checkInActivityRepository->byId(CheckInActivityId::fromString($selectedActivity));

            if ($activity === null) {
                continue;
            }

            $category = $this->checkInActivityCategoryRepository->byId($activity->categoryId());

            if ($category === null) {
                continue;
            }

            $activities[] = CheckInActivityData::fromDomain(
                activity: $activity,
                category: $category,
                locale: $locale,
            );
        }

        return $activities;
    }
}
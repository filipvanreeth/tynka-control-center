<?php
declare(strict_types=1);

namespace TynkaControlCenter\Services;

use DateTimeImmutable;
use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\Repositories\CheckInRepository;

class CheckInService
{
    private const MINIMUM_MINUTES_BETWEEN_CHECK_INS = 1;

    public function __construct(
        public readonly CheckInRepository $checkinRepository
    ) {
    }

    public function recordCheckIn(
        string $handler,
        bool $hasPeed,
        bool $hasPooped,
        bool $hadFood,
        bool $hadSnack,
        DateTimeImmutable $createdAt
    ): CheckIn {
        // $lastCheckIn = $this->checkinRepository->findLatestCheckIn();

        // if ($lastCheckIn === null) {
        //     $checkIn = CheckIn::create(
        //         handler: $handler,
        //         hasPeed: $hasPeed,
        //         hasPooped: $hasPooped,
        //         hadFood: $hadFood,
        //         hadSnack: $hadSnack,
        //         createdAt: $createdAt
        //     );

        //     return $this->checkinRepository->storeCheckIn($checkIn);
        // }

        // $lastCheckInTime = $lastCheckIn->getCreatedAt()->getTimestamp();
        // $currentTime = $createdAt->getTimestamp();

        // if (($currentTime - $lastCheckInTime) < self::MINIMUM_MINUTES_BETWEEN_CHECK_INS * 60) {
        //     throw new \Exception(sprintf(
        //         'A check-in has already been recorded %d minute(s) ago. Please wait before recording another check-in.',
        //         self::MINIMUM_MINUTES_BETWEEN_CHECK_INS
        //     ));
        // }

        $checkIn = CheckIn::create(
            handler: $handler,
            hasPeed: $hasPeed,
            hasPooped: $hasPooped,
            hadFood: $hadFood,
            hadSnack: $hadSnack,
            createdAt: $createdAt
        );

        return $this->checkinRepository->storeCheckIn($checkIn);
    }

    public function getCheckInById(string $uuid): ?CheckIn
    {
        return $this->checkinRepository->findByUuid($uuid);
    }

    public function getAllCheckIns(string $order = 'DESC', ?int $limit = null): array
    {
        return $this->checkinRepository->findAll($order, $limit);
    }

    public function getTopHandlers(int $limit = 5): array
    {
        return $this->checkinRepository->findTopHandlers($limit);
    }
}
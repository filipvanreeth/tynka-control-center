<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;

final class GetStatisticsForCheckInOption
{
    public function __construct(
        private readonly CheckInOptionRepository $checkInOptionRepository,
        private readonly \PDO $pdo,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(string $option): array
    {
        $foundOption = null;

        foreach ($this->checkInOptionRepository->findAll() as $validOption) {
            if ($validOption->slug()->value() === $option) {
                $foundOption = $validOption;
                break;
            }
        }
        
        if ($foundOption === null) {
            throw new \InvalidArgumentException('Invalid check-in option: ' . $option);
        }

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) as total
             FROM checkins
             WHERE $option = 1"
        );

        $statement->execute();

        $total = (int) $statement->fetchColumn();

        // StatisticsCheckInOptionData
        return [
            'name' => $foundOption->slug()->value(),
            'total' => $total,
        ];
    }
}
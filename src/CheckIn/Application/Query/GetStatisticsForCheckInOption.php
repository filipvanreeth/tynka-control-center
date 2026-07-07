<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;

final class GetStatisticsForCheckInOption
{
    public function __construct(
        private readonly CheckInOptionRepository $checkInOptionRepository,
        private readonly CheckInReadModel $readModel,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(string $option): array
    {
        $foundOption = null;

        foreach ($this->checkInOptionRepository->findAll() as $validOption) {
            if ($validOption->id()->toString() === $option) {
                $foundOption = $validOption;
                break;
            }
        }
        
        if ($foundOption === null) {
            throw new \InvalidArgumentException('Invalid check-in option: ' . $option);
        }

        $total = $this->readModel->totalForOption($option);

        // StatisticsCheckInOptionData
        return [
            'name' => $foundOption->id()->toString(),
            'total' => $total,
        ];
    }
}
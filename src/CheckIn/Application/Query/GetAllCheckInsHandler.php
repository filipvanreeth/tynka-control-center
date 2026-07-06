<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use DateTimeImmutable;
use Exception;
use TynkaControlCenter\CheckIn\Application\Query\AllCheckInsData;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;
use TynkaControlCenter\Common\Domain\Locale;
use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Handler\Application\Query\HandlerData;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final class GetAllCheckInsHandler
{
    public function __construct(
        private readonly \PDO $pdo,
        private readonly HandlerRepository $handlerRepository,
        private CheckInOptionRepository $checkInOptionRepository,
        private CheckInOptionCategoryRepository $checkInOptionCategoryRepository
    ) {
    }

    public function handle(GetAllCheckInsQuery $query): AllCheckInsData
    {
        $limit = -1;
        $order = 'DESC';
        $stmt = $this->pdo->query("SELECT * FROM checkins ORDER BY created_at {$order} LIMIT {$limit}");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        var_dump($rows);

        if (!$rows) {
            return new AllCheckInsData(
                checkIns: [],
                total: 0,
            );
        }

        $checkIns = array_map(
            fn($row): CheckInData => new CheckInData(
                id: $row['id'],
                uuid: $row['uuid'],
                handler: HandlerData::fromDomain(
                    $this->handlerRepository->findBySlug(
                        new Slug($row['handler'])
                    )
                ),
                options: $this->parseCheckInOptions($row, new Locale('en')),
                createdAt: (new DateTimeImmutable($row['created_at']))->format('Y-m-d H:i')
            ),
            $rows
        );

        var_dump($checkIns);

        $total = \count($checkIns);

        $results = new AllCheckInsData(
            checkIns: $checkIns,
            total: $total
        );

        return $results;
    }

    /**
     * @param array<string, mixed> $row
     * @return CheckInOptionData[]
     */
    private function parseCheckInOptions(array $row, Locale $locale): array
    {
        $selectedSlugs = array_keys(array_filter([
            'peed' => (bool) $row['peed'],
            'pooped' => (bool) $row['pooped'],
            'food' => (bool) $row['food'],
            'snack' => (bool) $row['snack'],
        ]));

        $options = [];

        foreach ($selectedSlugs as $selectedSlug) {
            $option = $this->checkInOptionRepository->findBySlug(new Slug($selectedSlug));

            if ($option === null) {
                throw new Exception('dddd');
            }

            $category = $this->checkInOptionCategoryRepository->findBySlug($option->categoryId());

            if ($category === null) {
                throw new Exception("Error Processing category", 1);
            }

            $options[] = CheckInOptionData::fromDomain(
                option: $option,
                category: $category,
                locale: $locale,
            );
        }

        return $options;
    }
}
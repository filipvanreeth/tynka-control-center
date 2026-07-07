<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use DateTimeImmutable;
use TynkaControlCenter\CheckIn\Application\Query\AllCheckInsData;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionCategoryRepository;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionId;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;
use TynkaControlCenter\Common\Domain\Locale;
use TynkaControlCenter\Handler\Application\Query\HandlerData;
use TynkaControlCenter\Handler\Domain\HandlerId;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final class GetAllCheckInsHandler
{
    public function __construct(
        private readonly CheckInReadModel $readModel,
        private readonly HandlerRepository $handlerRepository,
        private CheckInOptionRepository $checkInOptionRepository,
        private CheckInOptionCategoryRepository $checkInOptionCategoryRepository
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
                id: $row['id'],
                uuid: $row['uuid'],
                handler: $this->resolveHandler($row['handler']),
                options: $this->resolveOptions($row, $locale),
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

    private function resolveHandler(string $handlerId): HandlerData
    {
        $handler = $this->handlerRepository->byId(HandlerId::fromString($handlerId));

        if ($handler === null) {
            return new HandlerData(name: $handlerId, slug: $handlerId, avatar: null);
        }

        return HandlerData::fromDomain($handler);
    }

    /**
     * @param array<string, mixed> $row
     * @return CheckInOptionData[]
     */
    private function resolveOptions(array $row, Locale $locale): array
    {
        $selectedOptions = array_keys(
            array_filter([
                'peed' => (bool) $row['peed'],
                'pooped' => (bool) $row['pooped'],
                'food' => (bool) $row['food'],
                'snack' => (bool) $row['snack'],
            ])
        );

        $options = [];

        foreach ($selectedOptions as $selectedOption) {
            $option = $this->checkInOptionRepository->byId(CheckInOptionId::fromString($selectedOption));

            if ($option === null) {
                continue;
            }

            $category = $this->checkInOptionCategoryRepository->byId($option->categoryId());

            if ($category === null) {
                continue;
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
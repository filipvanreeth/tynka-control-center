<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

interface CheckInReadModel
{
    /**
     * All check-in rows, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public function all(): array;

    /**
     * Single check-in row by its uuid, or null when it does not exist.
     *
     * @return array{
     *     uuid: string,
     *     handler: string,
     *     peed: bool,
     *     pooped: bool,
     *     food: bool,
     *     snack: bool,
     *     created_at: string,
     * }|null
     */
    public function byId(string $id): ?array;

    /**
     * Number of check-ins for which the given activity is set.
     */
    public function totalForActivity(string $activity): int;

    /**
     * Check-in counts grouped by handler, highest first.
     *
     * @return list<array{handler: string, total: int}>
     */
    public function handlerCounts(int $limit): array;
}

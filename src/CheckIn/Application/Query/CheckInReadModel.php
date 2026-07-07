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
     * Number of check-ins for which the given option is set.
     */
    public function totalForOption(string $option): int;

    /**
     * Check-in counts grouped by handler, highest first.
     *
     * @return list<array{handler: string, total: int}>
     */
    public function handlerCounts(int $limit): array;
}

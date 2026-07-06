<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

final readonly class AllCheckInsData
{
    /**
     * @param list<int, CheckInData> $checkIns
     * @param int $total
     */
    public function __construct(
        public array $checkIns,
        public int $total,
    ) {
    }
}
<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

final readonly class StatisticsCheckInActivityData
{
    public function __construct(
        public string $id,
        public string $title,
        public int $total,
    ) {
    }
}

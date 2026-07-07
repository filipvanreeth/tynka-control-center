<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

final readonly class GetStatisticsForCheckInActivityQuery
{
    public function __construct(
        public string $activity,
        public string $locale,
    ) {
    }
}

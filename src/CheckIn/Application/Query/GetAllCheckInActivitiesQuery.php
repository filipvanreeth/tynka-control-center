<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

final readonly class GetAllCheckInActivitiesQuery
{
    public function __construct(
        public string $locale,
    ) {
    }
}
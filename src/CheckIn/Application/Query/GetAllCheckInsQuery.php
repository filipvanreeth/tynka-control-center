<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

final class GetAllCheckInsQuery
{
    public function __construct(
        public readonly string $locale,
    ) {
    }
}
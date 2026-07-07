<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

final class GetCheckInByIdQuery
{
    public function __construct(
        public readonly string $id,
        public readonly string $locale,
    ) {
    }
}
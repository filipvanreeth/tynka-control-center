<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\Handler\Application\Query\HandlerData;

final readonly class CheckInData
{
    public function __construct(
        public string $id,
        public HandlerData $handler,
        public array $activities,
        public string $createdAt,
    ) {
    }
}

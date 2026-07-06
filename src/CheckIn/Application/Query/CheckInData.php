<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\Handler\Application\Query\HandlerData;

final readonly class CheckInData
{
    public function __construct(
        public ?int $id,
        public string $uuid,
        public HandlerData $handler,
        public array $options,
        public string $createdAt,
    ) {
    }
}

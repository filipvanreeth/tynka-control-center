<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

final readonly class TopHandlerData
{
    public function __construct(
        public HandlerData $handler,
        public int $total,
    ) {
    }
}

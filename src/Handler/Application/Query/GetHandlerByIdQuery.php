<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

final readonly class GetHandlerByIdQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}

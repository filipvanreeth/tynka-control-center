<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Application\Query;

final readonly class TopUserData
{
    public function __construct(
        public UserData $handler,
        public int $total,
    ) {
    }
}

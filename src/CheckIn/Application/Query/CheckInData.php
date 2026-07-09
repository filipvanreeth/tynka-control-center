<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\User\Application\Query\UserData;

final readonly class CheckInData
{
    /**
     * @param list<CheckInActivityData> $activities
     */
    public function __construct(
        public string $id,
        public UserData $handler,
        public array $activities,
        public string $createdAt,
    ) {
    }
}

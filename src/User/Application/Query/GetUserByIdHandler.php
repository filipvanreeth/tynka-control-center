<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Application\Query;

use TynkaControlCenter\User\Domain\UserId;
use TynkaControlCenter\User\Domain\UserRepository;

final class GetUserByIdHandler
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function handle(GetUserByIdQuery $query): ?UserData
    {
        $handler = $this->userRepository->byId(
            UserId::fromString($query->id)
        );

        return $handler !== null ? UserData::fromDomain($handler) : null;
    }
}

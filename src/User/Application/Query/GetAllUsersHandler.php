<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Application\Query;

use TynkaControlCenter\User\Domain\User;
use TynkaControlCenter\User\Domain\UserRepository;

final readonly class GetAllUsersHandler
{
    public function __construct(
        public readonly UserRepository $userRepository
    )
    {
    }

    /**
     * @return list<UserData>
     */
    public function handle(): array
    {
        return array_map(
            function (User $handler): UserData {
                return UserData::fromDomain($handler);
            },
            $this->userRepository->findAll()
        );
    }
}

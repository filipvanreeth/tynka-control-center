<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Application\Query;

use TynkaControlCenter\CheckIn\Application\Query\CheckInReadModel;
use TynkaControlCenter\User\Domain\UserRepository;

final class GetTopUsersHandler
{
    public function __construct(
        private readonly CheckInReadModel $readModel,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * @return list<TopUserData>
     */
    public function handle(): array
    {
        $handlers = $this->readModel->handlerCounts(5);
        $getUserById = new GetUserByIdHandler($this->userRepository);

        return array_map(
            function (array $row) use ($getUserById): TopUserData {
                $handler = $getUserById->handle(
                    new GetUserByIdQuery($row['handler'])
                );

                return new TopUserData(
                    handler: $handler ?? new UserData(
                        name: $row['handler'],
                        id: $row['handler'],
                        avatar: null,
                    ),
                    total: (int) $row['total'],
                );
            },
            $handlers
        );
    }
}

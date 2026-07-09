<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Infrastructure;

use TynkaControlCenter\Common\Domain\Avatar;
use TynkaControlCenter\User\Domain\User;
use TynkaControlCenter\User\Domain\UserId;
use TynkaControlCenter\User\Domain\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    public function byId(UserId $id): ?User
    {
        foreach ($this->findAll() as $handler) {
            if ($handler->id()->equals($id)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @return list<User>
     */
    public function findAll(): array
    {
        return [
            User::reconstitute(
                UserId::fromString('filip'),
                'Filip',
                new Avatar('abstract-avatar-01.jpg'),
            ),
            User::reconstitute(
                UserId::fromString('nathalie'),
                'Nathalie',
                new Avatar('abstract-avatar-02.jpg'),
            ),
            User::reconstitute(
                UserId::fromString('maya'),
                'Maya',
                new Avatar('abstract-avatar-03.jpg'),
            ),
            User::reconstitute(
                UserId::fromString('eline'),
                'Eline',
                new Avatar('abstract-avatar-04.jpg'),
            ),
            User::reconstitute(
                UserId::fromString('xander'),
                'Xander',
                new Avatar('abstract-avatar-05.jpg'),
            ),
            User::reconstitute(
                UserId::fromString('dog-sitter'),
                'Dog Sitter',
                new Avatar('abstract-avatar-05.jpg'),
            ),
        ];
    }
}

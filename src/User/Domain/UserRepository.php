<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Domain;

interface UserRepository
{
    /**
     * @return list<User>
     */
    public function findAll(): array;

    public function byId(UserId $id): ?User;
}

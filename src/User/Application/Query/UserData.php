<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Application\Query;

use TynkaControlCenter\User\Domain\User;

final readonly class UserData
{
    public function __construct(
        public string $name,
        public string $id,
        public ?string $avatar,
    ) {
    }

    public static function fromDomain(User $domain): self
    {
        return new self(
            name: $domain->name(),
            id: $domain->id()->toString(),
            avatar: $domain->avatar()?->path()
        );
    }
}
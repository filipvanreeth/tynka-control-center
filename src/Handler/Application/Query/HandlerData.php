<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

use TynkaControlCenter\Handler\Domain\Handler;

final readonly class HandlerData
{
    public function __construct(
        public string $name,
        public string $id,
        public ?string $avatar,
    ) {
    }

    public static function fromDomain(Handler $domain): self
    {
        return new self(
            name: $domain->name(),
            id: $domain->id()->toString(),
            avatar: $domain->avatar()?->path()
        );
    }
}
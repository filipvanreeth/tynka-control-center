<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Domain;

use TynkaControlCenter\Common\Domain\Avatar;

final class User
{
    private function __construct(
        private UserId $id,
        private string $name,
        private ?Avatar $avatar,
    ) {
    }

    public static function reconstitute(
        UserId $id,
        string $name,
        ?Avatar $avatar,
    ): self {
        return new self($id, $name, $avatar);
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function avatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }
}

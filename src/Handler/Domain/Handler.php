<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Domain;

use TynkaControlCenter\Common\Domain\Avatar;

final class Handler
{
    private function __construct(
        private HandlerId $id,
        private string $name,
        private ?Avatar $avatar,
    ) {
    }

    public static function reconstitute(
        HandlerId $id,
        string $name,
        ?Avatar $avatar,
    ): self {
        return new self($id, $name, $avatar);
    }

    public function id(): HandlerId
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

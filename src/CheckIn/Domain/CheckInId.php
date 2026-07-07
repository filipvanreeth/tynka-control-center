<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use Ramsey\Uuid\Uuid;

final class CheckInId
{
    private function __construct(private readonly string $value)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid7()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

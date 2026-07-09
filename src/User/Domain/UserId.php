<?php

declare(strict_types=1);

namespace TynkaControlCenter\User\Domain;

final class UserId
{
    private function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw InvalidUserId::emptyValue();
        }
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

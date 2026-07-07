<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Domain;

final class HandlerId
{
    private function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw InvalidHandlerId::emptyValue();
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

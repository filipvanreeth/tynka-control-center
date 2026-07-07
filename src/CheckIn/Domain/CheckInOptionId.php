<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

final class CheckInOptionId
{
    private function __construct(private readonly string $value)
    {
        if (trim($value) === '') {
            throw InvalidCheckInOptionId::emptyValue();
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

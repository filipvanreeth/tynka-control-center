<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

final class Slug
{
    public function __construct(
        private string $value,
    ) {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Value cannot be empty');
        }

        if (
            !preg_match(
                '/^[a-zA-Z0-9_-]+$/',
                $value
            )
        ) {
            throw new \InvalidArgumentException(
                "{$value} may only contain letters, numbers, underscores and dashes."
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
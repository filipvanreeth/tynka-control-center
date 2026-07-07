<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

final class Slug
{
    public function __construct(
        private string $value,
    ) {
        if (trim($value) === '') {
            throw InvalidSlug::emptyValue();
        }

        if (
            !preg_match(
                '/^[a-zA-Z0-9_-]+$/',
                $value
            )
        ) {
            throw InvalidSlug::invalidCharacters($value);
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
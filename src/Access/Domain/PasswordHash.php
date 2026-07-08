<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Domain;

final class PasswordHash
{
    private function __construct(private readonly string $hash)
    {
    }

    public static function fromPlainText(string $plainText): self
    {
        return new self(
            password_hash(
                $plainText,
                PASSWORD_DEFAULT
            )
        );
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function verify(string $plainText): bool
    {
        return password_verify(
            $plainText,
            $this->hash
        );
    }

    public function toString(): string
    {
        return $this->hash;
    }
}

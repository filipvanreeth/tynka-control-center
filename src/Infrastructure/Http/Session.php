<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Http;

interface Session
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    /**
     * Vernieuwt het sessie-id met behoud van de data. Aanroepen bij een
     * privilege-wissel (login/logout) tegen session fixation.
     */
    public function regenerate(): void;

    public function flash(string $type, string $message): void;

    /**
     * Leest de flash-boodschap en verwijdert ze meteen (read-once).
     *
     * @return array{type: string, message: string}|null
     */
    public function pullFlash(): ?array;
}

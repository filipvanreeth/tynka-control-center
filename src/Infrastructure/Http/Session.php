<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Http;

/**
 * Poort naar de sessie.
 *
 * Waarom een aparte poort? PSR-7 modelleert een enkele request/response en zegt
 * bewust niets over sessies (server-state tussen requests). Door de sessie achter
 * een interface te zetten blijft de controller vrij van `$_SESSION` en dus
 * testbaar — in een test injecteer je een in-memory implementatie.
 */
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

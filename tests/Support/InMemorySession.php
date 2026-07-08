<?php

declare(strict_types=1);

namespace App\Tests\Support;

use TynkaControlCenter\Infrastructure\Http\Session;

/**
 * In-memory implementatie van de {@see Session}-poort voor tests. Bewijst de winst
 * van de poort: geen `$_SESSION`, geen actieve PHP-sessie nodig om gedrag te testen.
 */
final class InMemorySession implements Session
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function flash(string $type, string $message): void
    {
        $this->data['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * @return array{type: string, message: string}|null
     */
    public function pullFlash(): ?array
    {
        $flash = $this->data['flash'] ?? null;
        unset($this->data['flash']);

        if (!\is_array($flash) || !isset($flash['type'], $flash['message'])) {
            return null;
        }

        return ['type' => (string) $flash['type'], 'message' => (string) $flash['message']];
    }
}

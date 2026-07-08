<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Http;

/**
 * Adapter die de poort {@see Session} implementeert bovenop de PHP-superglobal
 * `$_SESSION`. De sessie zelf wordt gestart in de front controller; deze klasse
 * leest en schrijft enkel. Dit is de enige plek (naast de front controller) waar
 * `$_SESSION` nog voorkomt.
 */
final class PhpSession implements Session
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * @return array{type: string, message: string}|null
     */
    public function pullFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        if (!\is_array($flash) || !isset($flash['type'], $flash['message'])) {
            return null;
        }

        return ['type' => (string) $flash['type'], 'message' => (string) $flash['message']];
    }
}

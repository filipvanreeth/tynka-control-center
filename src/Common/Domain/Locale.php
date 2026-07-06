<?php

declare(strict_types= 1);

namespace TynkaControlCenter\Common\Domain;

final class Locale
{
    public function __construct(
        private string $value,
    ) {
        if (!preg_match('/^[a-z]{2}$/', $value)) {
            throw InvalidLocale::forValue($value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}

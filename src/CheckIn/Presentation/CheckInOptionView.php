<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

final readonly class CheckInOptionView
{
    public function __construct(
        public string $icon,
        public string $label,
    ) {
    }
}
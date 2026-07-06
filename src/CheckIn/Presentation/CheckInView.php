<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

final readonly class CheckInView
{
    public function __construct(
        public string $avatar,
        public string $checkInDate,
        public string $handler,
        /** @var array<CheckInOptionView> */
        public array $options,
        public bool $isLatest,
    ) {
    }
}
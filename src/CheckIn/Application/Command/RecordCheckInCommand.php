<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Command;

use DateTimeImmutable;

final readonly class RecordCheckInCommand
{
    /**
     * @param list<string> $selectedOptions option slugs, e.g. ['peed', 'food']
     */
    public function __construct(
        public string $handler,
        public array $selectedOptions,
        public DateTimeImmutable $createdAt,
    ) {
    }
}

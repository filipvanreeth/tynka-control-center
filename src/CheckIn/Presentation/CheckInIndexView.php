<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

final readonly class CheckInIndexView
{
    public function __construct(
        public CheckInFormView $form,
        public array $checkIns,
        public array $checkInActivityStats,
        public array $topHandlers,
        public int $totalCheckIns,
        public ?array $flash,
    ) {
    }
}
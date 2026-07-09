<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use TynkaControlCenter\Handler\Application\Query\TopHandlerData;

final readonly class CheckInIndexView
{
    /**
     * @param list<CheckInCardData> $checkIns
     * @param list<array{total: int, label: string, colors: string}> $checkInActivityStats
     * @param list<TopHandlerData> $topHandlers
     * @param array{type: string, message: string}|null $flash
     */
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
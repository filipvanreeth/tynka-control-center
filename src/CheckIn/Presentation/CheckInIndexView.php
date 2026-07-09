<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use TynkaControlCenter\User\Application\Query\TopUserData;

final readonly class CheckInIndexView
{
    /**
     * @param list<CheckInCardData> $checkIns
     * @param list<array{total: int, label: string, colors: string}> $checkInActivityStats
     * @param list<TopUserData> $topUsers
     * @param array{type: string, message: string}|null $flash
     */
    public function __construct(
        public CheckInFormView $form,
        public array $checkIns,
        public array $checkInActivityStats,
        public array $topUsers,
        public int $totalCheckIns,
        public ?array $flash,
    ) {
    }
}
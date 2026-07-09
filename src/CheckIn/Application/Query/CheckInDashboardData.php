<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\User\Application\Query\UserData;
use TynkaControlCenter\User\Application\Query\TopUserData;

/**
 * Lees-DTO dat de check-in-dashboardpagina in één keer vult. Componeert data uit
 * de CheckIn-context met de top-users uit de User-context (cross-context
 * via DTO's, niet via entities). Bevat géén presentatie-bits: `flash`/locale
 * blijven in de controller.
 */
final readonly class CheckInDashboardData
{
    /**
     * @param list<UserData> $handlers
     * @param list<CheckInActivityData> $activities
     * @param list<CheckInData> $checkIns
     * @param list<TopUserData> $topUsers
     * @param list<array{total: int, label: string, colors: string}> $activityStats
     */
    public function __construct(
        public array $handlers,
        public array $activities,
        public array $checkIns,
        public int $totalCheckIns,
        public array $topUsers,
        public array $activityStats,
    ) {
    }
}

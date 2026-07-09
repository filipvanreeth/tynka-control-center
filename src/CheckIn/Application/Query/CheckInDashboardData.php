<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\Handler\Application\Query\HandlerData;
use TynkaControlCenter\Handler\Application\Query\TopHandlerData;

/**
 * Lees-DTO dat de check-in-dashboardpagina in één keer vult. Componeert data uit
 * de CheckIn-context met de top-handlers uit de Handler-context (cross-context
 * via DTO's, niet via entities). Bevat géén presentatie-bits: `flash`/locale
 * blijven in de controller.
 */
final readonly class CheckInDashboardData
{
    /**
     * @param list<HandlerData> $handlers
     * @param list<CheckInActivityData> $activities
     * @param list<CheckInData> $checkIns
     * @param list<TopHandlerData> $topHandlers
     * @param list<array{total: int, label: string, colors: string}> $activityStats
     */
    public function __construct(
        public array $handlers,
        public array $activities,
        public array $checkIns,
        public int $totalCheckIns,
        public array $topHandlers,
        public array $activityStats,
    ) {
    }
}

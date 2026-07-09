<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Application\Query;

use TynkaControlCenter\User\Application\Query\GetAllUsersHandler;
use TynkaControlCenter\User\Application\Query\GetTopUsersHandler;

/**
 * Read-use-case voor de check-in-dashboardpagina: bundelt de losse queries tot
 * één view-DTO, zodat de controller niet vier handlers hoeft te orkestreren.
 * Puur delegeren en samenvoegen — geen invarianten, geen presentatie.
 */
final class GetCheckInDashboardHandler
{
    public function __construct(
        private readonly GetAllUsersHandler $handlers,
        private readonly GetAllCheckInActivitiesHandler $activities,
        private readonly GetAllCheckInsHandler $checkIns,
        private readonly GetTopUsersHandler $topUsers,
    ) {
    }

    public function handle(GetCheckInDashboardQuery $query): CheckInDashboardData
    {
        $checkIns = $this->checkIns->handle(new GetAllCheckInsQuery($query->locale));

        return new CheckInDashboardData(
            handlers: $this->handlers->handle(),
            activities: $this->activities->handle(new GetAllCheckInActivitiesQuery($query->locale)),
            checkIns: $checkIns->checkIns,
            totalCheckIns: $checkIns->total,
            topUsers: $this->topUsers->handle(),
            activityStats: [],
        );
    }
}

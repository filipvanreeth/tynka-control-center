<?php

declare(strict_types=1);

namespace App\Tests\CheckIn\Application\Query;

use PDO;
use PHPUnit\Framework\TestCase;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInDashboardHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInDashboardQuery;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInActivityCategoryRepository;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInActivityRepository;
use TynkaControlCenter\CheckIn\Infrastructure\Persistence\PdoCheckInReadModel;
use TynkaControlCenter\User\Application\Query\GetAllUsersHandler;
use TynkaControlCenter\User\Application\Query\GetTopUsersHandler;
use TynkaControlCenter\User\Infrastructure\InMemoryUserRepository;

/**
 * Integratie-stijl (de sub-handlers zijn `final`, dus niet te stubben): met één
 * geseede check-in zijn de secties onderscheidbaar in aantal, zodat een verkeerde
 * veld-mapping (bv. checkIns ↔ topUsers omwisselen) opvalt.
 */
final class GetCheckInDashboardHandlerTest extends TestCase
{
    public function testComposesEverySectionIntoOneDto(): void
    {
        $userRepository = new InMemoryUserRepository();
        $activityRepository = new InMemoryCheckInActivityRepository();
        $categoryRepository = new InMemoryCheckInActivityCategoryRepository();

        $pdo = new PDO('sqlite::memory:');
        $pdo->exec(
            'CREATE TABLE checkins (
                id INTEGER PRIMARY KEY AUTOINCREMENT, uuid TEXT, handler TEXT,
                peed BOOLEAN, pooped BOOLEAN, food BOOLEAN, snack BOOLEAN, created_at DATETIME
            )'
        );
        $pdo->exec(
            "INSERT INTO checkins (uuid, handler, peed, pooped, food, snack, created_at)
             VALUES ('u1', 'filip', 1, 0, 0, 0, '2020-01-15 08:00:00')"
        );
        $readModel = new PdoCheckInReadModel($pdo, 'checkins');

        $dashboard = new GetCheckInDashboardHandler(
            new GetAllUsersHandler($userRepository),
            new GetAllCheckInActivitiesHandler($activityRepository, $categoryRepository),
            new GetAllCheckInsHandler($readModel, $userRepository, $activityRepository, $categoryRepository),
            new GetTopUsersHandler($readModel, $userRepository),
        );

        $data = $dashboard->handle(new GetCheckInDashboardQuery('en'));

        self::assertCount(1, $data->checkIns);
        self::assertSame(1, $data->totalCheckIns);
        self::assertCount(1, $data->topUsers);
        self::assertGreaterThan(1, \count($data->handlers));
        self::assertNotEmpty($data->activities);
        self::assertSame([], $data->activityStats);
    }
}

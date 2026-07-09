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
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Handler\Application\Query\GetTopHandlersHandler;
use TynkaControlCenter\Handler\Infrastructure\InMemoryHandlerRepository;

/**
 * Integratie-stijl (de sub-handlers zijn `final`, dus niet te stubben): met één
 * geseede check-in zijn de secties onderscheidbaar in aantal, zodat een verkeerde
 * veld-mapping (bv. checkIns ↔ topHandlers omwisselen) opvalt.
 */
final class GetCheckInDashboardHandlerTest extends TestCase
{
    public function testComposesEverySectionIntoOneDto(): void
    {
        $handlerRepository = new InMemoryHandlerRepository();
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
            new GetAllHandlersHandler($handlerRepository),
            new GetAllCheckInActivitiesHandler($activityRepository, $categoryRepository),
            new GetAllCheckInsHandler($readModel, $handlerRepository, $activityRepository, $categoryRepository),
            new GetTopHandlersHandler($readModel, $handlerRepository),
        );

        $data = $dashboard->handle(new GetCheckInDashboardQuery('en'));

        self::assertCount(1, $data->checkIns);
        self::assertSame(1, $data->totalCheckIns);
        self::assertCount(1, $data->topHandlers);
        self::assertGreaterThan(1, \count($data->handlers));
        self::assertNotEmpty($data->activities);
        self::assertSame([], $data->activityStats);
    }
}

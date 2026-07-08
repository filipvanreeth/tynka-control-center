<?php

declare(strict_types=1);

namespace App\Tests\CheckIn\Presentation;

use App\Tests\Support\InMemoryCheckInRepository;
use App\Tests\Support\InMemorySession;
use Nyholm\Psr7\ServerRequest;
use PDO;
use PHPUnit\Framework\TestCase;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdHandler;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInActivityCategoryRepository;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInActivityRepository;
use TynkaControlCenter\CheckIn\Infrastructure\Persistence\PdoCheckInReadModel;
use TynkaControlCenter\CheckIn\Presentation\CheckInController;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Handler\Application\Query\GetTopHandlersHandler;
use TynkaControlCenter\Handler\Infrastructure\InMemoryHandlerRepository;
use TynkaControlCenter\Infrastructure\Http\Session;
use TynkaControlCenter\Infrastructure\Templating\TemplateEngine;

/**
 * Bewijst de kern van Fase 1: een controller-actie is een functie van Request →
 * Response, testbaar zonder webserver, superglobals of output-buffering.
 */
final class CheckInControllerTest extends TestCase
{
    public function testSubmittingACheckInRedirectsWithSuccessFlash(): void
    {
        $session = new InMemorySession();
        $controller = $this->makeController($session);

        $request = (new ServerRequest('POST', '/checkin'))->withParsedBody([
            'handler' => '1',
            'checkInAt' => '2026-07-08T10:00',
            'peed' => '1', // minstens één activiteit: vereist door de invariant
        ]);

        $response = $controller->handleCheckInSubmission($request);

        // De actie geeft data terug (302 + Location), geen side-effect.
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('http://localhost/', $response->getHeaderLine('Location'));

        // Flash liep via de Session-poort, niet via $_SESSION.
        $flash = $session->pullFlash();
        if ($flash === null) {
            self::fail('Verwachtte een flash-boodschap na een geslaagde check-in.');
        }
        self::assertSame('success', $flash['type']);
    }

    /**
     * Assembleert de controller met test-dubbels. De hoeveelheid bedrading hier is
     * zelf een signaal: de controller heeft te veel collaborators (God-controller)
     * en zou opgesplitst mogen worden in single-action controllers (backlog).
     */
    private function makeController(Session $session): CheckInController
    {
        $handlerRepository = new InMemoryHandlerRepository();
        $activityRepository = new InMemoryCheckInActivityRepository();
        $categoryRepository = new InMemoryCheckInActivityCategoryRepository();
        $readModel = new PdoCheckInReadModel(new PDO('sqlite::memory:'), 'checkins');

        return new CheckInController(
            recordCheckInHandler: new RecordCheckInHandler(new InMemoryCheckInRepository()),
            translator: $this->createStub(Translator::class),
            appConfig: new AppConfig(
                appUrl: 'http://localhost',
                appVersion: 'test',
                dbDriver: 'sqlite',
                dbDatabase: ':memory:',
            ),
            viewRenderer: $this->createStub(TemplateEngine::class),
            getCheckInHandler: new GetCheckInByIdHandler(
                $readModel,
                $handlerRepository,
                $activityRepository,
                $categoryRepository,
            ),
            getAllHandlersHandler: new GetAllHandlersHandler($handlerRepository),
            getAllCheckInActivitiesHandler: new GetAllCheckInActivitiesHandler(
                $activityRepository,
                $categoryRepository,
            ),
            getTopHandlersHandler: new GetTopHandlersHandler($readModel, $handlerRepository),
            getAllCheckInsHandler: new GetAllCheckInsHandler(
                $readModel,
                $handlerRepository,
                $activityRepository,
                $categoryRepository,
            ),
            session: $session,
            activityRepository: $activityRepository,
        );
    }
}

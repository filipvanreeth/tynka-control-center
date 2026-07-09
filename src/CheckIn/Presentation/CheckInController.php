<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInDashboardHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInDashboardQuery;
use TynkaControlCenter\CheckIn\Domain\CheckInActivity;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Infrastructure\Http\Session;
use TynkaControlCenter\Infrastructure\Templating\TemplateEngine;

/**
 * Elke actie is een functie van Request → Response: input komt binnen als
 * argument, output gaat als returnwaarde naar buiten. Geen superglobals, geen
 * `echo`, geen `exit()` — daardoor is de controller unit-testbaar en weet hij
 * niets meer over de HTTP-machinerie (die zit in de front controller + emitter).
 */
class CheckInController
{
    public function __construct(
        private readonly RecordCheckInHandler $recordCheckInHandler,
        public readonly Translator $translator,
        public readonly AppConfig $appConfig,
        public readonly TemplateEngine $viewRenderer,
        public readonly GetCheckInByIdHandler $getCheckInHandler,
        public readonly GetAllHandlersHandler $getAllHandlersHandler,
        public readonly GetAllCheckInActivitiesHandler $getAllCheckInActivitiesHandler,
        private readonly GetCheckInDashboardHandler $dashboard,
        private readonly Session $session,
        private readonly CheckInActivityRepository $activityRepository,
    ) {
    }

    public function handleCheckInSubmission(ServerRequestInterface $request): ResponseInterface
    {
        $redirectUrl = $this->appConfig->appUrl . "/";
        $locale = \is_string($this->session->get('locale'))
            ? $this->session->get('locale')
            : 'en';

        $form = RecordCheckInForm::fromRequest(
            body: (array) $request->getParsedBody(),
            activityIds: array_map(
                static fn(CheckInActivity $activity): string => $activity->id()->toString(),
                $this->activityRepository->findAll(),
            )
        );

        if ($form->command === null) {
            $messages = array_map(
                fn(string $code): string => $this->translator->translate($code, $locale),
                $form->errors,
            );
            $this->session->flash("error", implode(' ', $messages));

            return new Response(302, ["Location" => $redirectUrl]);
        }

        try {
            $this->recordCheckInHandler->handle($form->command);
        } catch (\Exception $e) {
            // Vangnet voor domein-invarianten die de vorm-validatie niet dekt.
            $this->session->flash("error", $e->getMessage());

            return new Response(302, ["Location" => $redirectUrl]);
        }

        $this->session->flash("success", $this->translator->translate('check_in.recorded', $locale));

        return new Response(302, ["Location" => $redirectUrl]);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $locale = \is_string($this->session->get('locale'))
            ? $this->session->get('locale')
            : 'en';

        $dashboard = $this->dashboard->handle(new GetCheckInDashboardQuery($locale));

        $view = new CheckInIndexView(
            form: new CheckInFormView(
                action: "/checkin",
                handlers: $dashboard->handlers,
                activities: $dashboard->activities,
                data: null,
            ),
            checkIns: $dashboard->checkIns,
            checkInActivityStats: $dashboard->activityStats,
            topHandlers: $dashboard->topHandlers,
            totalCheckIns: $dashboard->totalCheckIns,
            flash: $this->session->pullFlash(),
        );

        return new Response(200, [], $this->viewRenderer->render('check-ins/index', [
            'view' => $view,
        ]));
    }

    public function edit(ServerRequestInterface $request): ResponseInterface
    {
        $locale = \is_string($this->session->get('locale'))
            ? $this->session->get('locale')
            : 'en';

        $checkIn = $this->getCheckInHandler->handle(
            new GetCheckInByIdQuery((string) $request->getAttribute('id'), $locale),
        );

        if ($checkIn === null) {
            return new Response(404);
        }

        $formData = new CheckInFormView(
            action: "checkin/{$checkIn->id}/edit",
            handlers: $this->getAllHandlersHandler->handle(),
            activities: $this->getAllCheckInActivitiesHandler->handle(
                new GetAllCheckInActivitiesQuery(locale: $locale)
            ),
            data: $checkIn,
        );

        $html = $this->viewRenderer->render('check-ins/edit', [
            'form' => $formData,
        ]);

        return new Response(200, [], $html);
    }
}

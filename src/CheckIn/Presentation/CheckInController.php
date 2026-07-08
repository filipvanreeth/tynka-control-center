<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdQuery;
use TynkaControlCenter\CheckIn\Domain\CheckInActivity;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityRepository;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Handler\Application\Query\GetTopHandlersHandler;
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
        public readonly GetTopHandlersHandler $getTopHandlersHandler,
        private readonly GetAllCheckInsHandler $getAllCheckInsHandler,
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

        $checkInForm = new CheckInFormView(
            action: "/checkin",
            handlers: $this->getAllHandlersHandler->handle(),
            activities: $this->getAllCheckInActivitiesHandler->handle(
                new GetAllCheckInActivitiesQuery(locale: $locale)
            ),
            data: null,
        );

        $allCheckIns = $this->getAllCheckInsHandler->handle(
            new GetAllCheckInsQuery(locale: $locale)
        );

        $indexModel = new CheckInIndexView(
            form: $checkInForm,
            checkIns: $allCheckIns->checkIns,
            checkInActivityStats: [],
            topHandlers: $this->getTopHandlersHandler->handle(),
            totalCheckIns: $allCheckIns->total,
            flash: $this->session->pullFlash(),
        );

        $html = $this->viewRenderer->render("check-ins/index", [
            "form" => $indexModel->form,
            "checkIns" => $indexModel->checkIns,
            "checkInActivities" => $indexModel->form->activities,
            "checkInActivityStats" => $indexModel->checkInActivityStats,
            "topHandlers" => $indexModel->topHandlers,
            "totalCheckIns" => $indexModel->totalCheckIns,
            "flash" => $indexModel->flash,
        ]);

        return new Response(200, [], $html);
    }

    public function edit(ServerRequestInterface $request): ResponseInterface
    {
        $checkIn = $this->getCheckInHandler->handle(
            new GetCheckInByIdQuery((string) $request->getAttribute('id')),
        );

        $formData = new CheckInFormView(
            action: "checkin/{$checkIn->id}/edit",
            handlers: $this->getAllHandlersHandler->handle(),
            activities: $this->getAllCheckInActivitiesHandler->handle(),
            data: $checkIn,
        );

        $html = $this->viewRenderer->render(
            template: "check-ins/edit",
            data: [
                "htmlLang" => $this->translator->getHtmlLang(),
                "form" => $formData,
                "viewRenderer" => $this->viewRenderer,
            ],
        );

        return new Response(200, [], $html);
    }
}

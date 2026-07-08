<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use DateTimeImmutable;
use DateTimeZone;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInCommand;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdQuery;
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
    ) {
    }

    public function handleCheckInSubmission(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<string, mixed> $body */
        $body = (array) $request->getParsedBody();

        // TODO (backlog B): input-validatie — $body['checkInAt']/'handler' worden
        // nu nog blind vertrouwd; een ontbrekende waarde geeft een TypeError.
        $createdAt = DateTimeImmutable::createFromFormat(
            "Y-m-d\TH:i",
            (string) $body["checkInAt"],
            new DateTimeZone("Europe/Brussels"),
        )->setTimezone(new DateTimeZone("UTC"));

        $redirectUrl = $this->appConfig->appUrl . "/";

        $selectedActivities = [];
        foreach (["peed", "pooped", "food", "snack"] as $activitySlug) {
            if (!empty($body[$activitySlug])) {
                $selectedActivities[] = $activitySlug;
            }
        }

        try {
            $this->recordCheckInHandler->handle(
                new RecordCheckInCommand(
                    handler: (string) $body["handler"],
                    selectedActivities: $selectedActivities,
                    createdAt: $createdAt,
                ),
            );
        } catch (\Exception $e) {
            $this->session->flash("error", $e->getMessage());

            return new Response(302, ["Location" => $redirectUrl]);
        }

        $this->session->flash("success", "Check-in successful registered.");

        return new Response(302, ["Location" => $redirectUrl]);
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $locale = \is_string($this->session->get('locale'))
            ? $this->session->get('locale')
            : 'en';

        $checkInForm = new CheckInFormView(
            action: "action",
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
            action: "checkin/{$checkIn->uuid}/edit",
            handlers: $this->getAllHandlersHandler->handle(),
            activities: $this->getAllCheckInActivitiesHandler->handle(),
            data: $checkIn,
        );

        $html = $this->viewRenderer->render(
            path: "check-ins/edit",
            data: [
                "htmlLang" => $this->translator->getHtmlLang(),
                "form" => $formData,
                "viewRenderer" => $this->viewRenderer,
            ],
        );

        return new Response(200, [], $html);
    }
}

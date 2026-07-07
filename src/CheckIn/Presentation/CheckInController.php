<?php
declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use DateTimeImmutable;
use DateTimeZone;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInActivitiesQuery;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInCommand;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInByIdQuery;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Handler\Application\Query\GetTopHandlersHandler;
use TynkaControlCenter\Infrastructure\Templating\TemplateEngine;
use TynkaControlCenter\Common\Domain\Translator;

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
        private GetAllCheckInsHandler $getAllCheckInsHandler,
    ) {
    }

    public function handleCheckInSubmission(): void
    {
        $createdAt = DateTimeImmutable::createFromFormat(
            "Y-m-d\TH:i",
            $_POST["checkInAt"],
            new DateTimeZone("Europe/Brussels"),
        )->setTimezone(new DateTimeZone("UTC"));

        $redirectUrl = $this->appConfig->appUrl . "/";

        $selectedActivities = [];
        foreach (["peed", "pooped", "food", "snack"] as $activitySlug) {
            if (!empty($_POST[$activitySlug])) {
                $selectedActivities[] = $activitySlug;
            }
        }

        try {
            $this->recordCheckInHandler->handle(
                new RecordCheckInCommand(
                    handler: $_POST["handler"],
                    selectedActivities: $selectedActivities,
                    createdAt: $createdAt,
                ),
            );
        } catch (\Exception $e) {
            $_SESSION["flash"] = [
                "type" => "error",
                "message" => $e->getMessage(),
            ];

            $this->redirectTo($redirectUrl);
        }

        $_SESSION["flash"] = [
            "type" => "success",
            "message" => "Check-in successful registered.",
        ];

        $this->redirectTo($redirectUrl);
    }

    public function index(): void
    {
        $locale = \is_string($_SESSION['locale'] ?? null)
            ? $_SESSION['locale']
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
            flash: $this->pullFlashFromSession(),
        );

        echo $this->viewRenderer->render("check-ins/index", [
            "form" => $indexModel->form,
            "checkIns" => $indexModel->checkIns,
            "checkInActivities" => $indexModel->form->activities,
            "checkInActivityStats" => $indexModel->checkInActivityStats,
            "topHandlers" => $indexModel->topHandlers,
            "totalCheckIns" => $indexModel->totalCheckIns,
            "flash" => $indexModel->flash,
        ]);
    }

    /**
     * Edits check-in data.
     * @param array<string, mixed> $vars
     * @return void
     */
    public function edit(array $vars): void
    {
        $checkIn = $this->getCheckInHandler->handle(
            new GetCheckInByIdQuery($vars["id"]),
        );

        $formData = new CheckInFormView(
            action: "checkin/{$checkIn->uuid}/edit",
            handlers: $this->getAllHandlersHandler->handle(),
            activities: $this->getAllCheckInActivitiesHandler->handle(),
            data: $checkIn,
        );

        $htmlLang = $this->translator->getHtmlLang();

        echo $this->viewRenderer->render(
            path: "check-ins/edit",
            data: [
                "htmlLang" => $htmlLang,
                "form" => $formData,
                "viewRenderer" => $this->viewRenderer,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function pullFlashFromSession(): ?array
    {
        $flash = $_SESSION["flash"] ?? null;
        unset($_SESSION["flash"]);

        return \is_array($flash) ? $flash : null;
    }

    private function redirectTo(string $url): void
    {
        header("Location: $url");
        exit();
    }
}

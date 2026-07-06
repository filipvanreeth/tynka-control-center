<?php
declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use DateTimeImmutable;
use DateTimeZone;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInOptionsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInOptionsQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetAllCheckInsQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInHandler;
use TynkaControlCenter\CheckIn\Application\Query\GetCheckInQuery;
use TynkaControlCenter\CheckIn\Application\Query\GetStatisticsForCheckInOption;
use TynkaControlCenter\CheckIn\Domain\CheckInOptionRepository;
use TynkaControlCenter\CheckIn\Infrastructure\InMemoryCheckInOptionRepository;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Application\Query\GetAllHandlersHandler;
use TynkaControlCenter\Handler\Application\Query\GetTopHandlersHandler;
use TynkaControlCenter\Handler\Infrastructure\InMemoryHandlerRepository;
use TynkaControlCenter\Infrastructure\PhpTemplateEngine;
use TynkaControlCenter\Services\CheckInService;
use TynkaControlCenter\Services\HandlerService;
use TynkaControlCenter\Common\Domain\Translator;

class CheckInController
{
    public function __construct(
        public readonly CheckInService $checkInService,
        public readonly HandlerService $handlerService,
        public readonly Translator $translator,
        public readonly AppConfig $appConfig,
        public readonly PhpTemplateEngine $viewRenderer,
        public readonly GetCheckInHandler $getCheckInHandler,
        public readonly GetAllHandlersHandler $getAllHandlersHandler,
        public readonly GetAllCheckInOptionsHandler $getAllCheckInOptionsHandler,
        public readonly \PDO $pdo,
        public readonly CheckInOptionRepository $checkInOptionRepository,
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

        try {
            $this->checkInService->recordCheckIn(
                handler: $_POST["handler"],
                hasPeed: !empty($_POST["peed"]),
                hasPooped: !empty($_POST["pooped"]),
                hadFood: !empty($_POST["food"]),
                hadSnack: !empty($_POST["snack"]),
                createdAt: $createdAt,
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
            options: $this->getAllCheckInOptionsHandler->handle(
                new GetAllCheckInOptionsQuery(locale: $locale)
            ),
            data: null,
        );

        $allCheckIns = $this->getAllCheckInsHandler->handle(
            new GetAllCheckInsQuery(locale: $locale)
        );

        $indexModel = new CheckInIndexView(
            form: $checkInForm,
            checkIns: $allCheckIns->checkIns,
            checkInOptionStats: [],
            topHandlers: (new GetTopHandlersHandler(
                pdo: $this->pdo,
                handlerRepository: new InMemoryHandlerRepository(),
            ))->handle(),
            totalCheckIns: $allCheckIns->total,
            flash: $this->pullFlashFromSession(),
        );

        $statistics = (new GetStatisticsForCheckInOption(
            $this->checkInOptionRepository,
            $this->pdo,
        ))->handle('peed');

        // Old code.

        $handlers = $this->getAllHandlersHandler->handle();
        $checkIns = $this->getAllCheckInsHandler->handle(
            new GetAllCheckInsQuery(locale: $locale)
        );
        die();
        // $checkIns = $this->checkInService->getAllCheckIns(
        //     order: "DESC",
        //     limit: 10,
        // );
        // $checkInOptions = CheckInOptions::forForm($this->translationService);
        // $htmlLang = $this->translationService->getHtmlLang();
        // $flash = $this->pullFlashFromSession();

        // header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        // header("Pragma: no-cache");



        // $checkInOptionStats = [];
        // $checkInOptionStats[] = [
        //     "label" => $this->translationService->translate("Peed"),
        //     "total" => $this->checkInService->checkinRepository->findTotalsByCheckInOption(
        //         "peed",
        //     )[0]["total"],
        //     "colors" => "bg-dairy-cream-200 border-dairy-cream-200/50",
        // ];
        // $checkInOptionStats[] = [
        //     "label" => $this->translationService->translate("Pooped"),
        //     "total" => $this->checkInService->checkinRepository->findTotalsByCheckInOption(
        //         "pooped",
        //     )[0]["total"],
        //     "colors" => "bg-opal-800 border-opal-800/50",
        // ];
        // $checkInOptionStats[] = [
        //     "label" => $this->translationService->translate("Snacks"),
        //     "total" => $this->checkInService->checkinRepository->findTotalsByCheckInOption(
        //         "snack",
        //     )[0]["total"],
        //     "colors" => "bg-periwinkle-800 border-periwinkle-800/50",
        // ];

        echo $this->viewRenderer->render("check-ins/index", [
            "handlers" => $handlers,
            "checkIns" => $checkIns,
            "checkInOptions" => $checkInOptions,
            "htmlLang" => $htmlLang,
            "form" => [
                "action" => "checkin",
            ],
            "viewRenderer" => $this->viewRenderer,
            "handlerService" => $this->handlerService,
            "topHandlers" => $this->checkInService->getTopHandlers(limit: 5),
            "flash" => $flash,
            "checkInOptionStats" => $checkInOptionStats,
            "totalCheckIns" => count(
                $this->checkInService->getAllCheckIns(order: "DESC"),
            ),
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
            new GetCheckInQuery($vars["id"]),
        );

        $formData = new CheckInFormView(
            action: "checkin/{$checkIn->uuid}/edit",
            handlers: $this->getAllHandlersHandler->handle(),
            options: $this->getAllCheckInOptionsHandler->handle(),
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

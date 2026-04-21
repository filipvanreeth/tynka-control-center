<?php
declare(strict_types=1);

namespace TynkaControlCenter\Controllers;

use DateTimeImmutable;
use DateTimeZone;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Enums\CheckInOptions;
use TynkaControlCenter\Presentation\ViewRenderer;
use TynkaControlCenter\Services\HandlerService;
use TynkaControlCenter\Services\TranslationService;
use TynkaControlCenter\Services\CheckInService;

class CheckInController
{
    public function __construct(
        private readonly CheckInService $checkInService,
        private readonly HandlerService $handlerService,
        private readonly TranslationService $translationService,
        private readonly AppConfig $appConfig,
        private readonly ViewRenderer $viewRenderer
    ) {
    }

    public function handleCheckInSubmission(): void
    {
        $createdAt = DateTimeImmutable::createFromFormat(
            'Y-m-d\TH:i',
            $_POST['checkInAt'],
            new DateTimeZone('Europe/Brussels')
        )->setTimezone(new DateTimeZone('UTC'));
        
        $redirectUrl = $this->appConfig->appUrl . '/';

        try {
            $this->checkInService->recordCheckIn(
                handler: $_POST['handler'],
                hasPeed: !empty($_POST['peed']),
                hasPooped: !empty($_POST['pooped']),
                hadFood: !empty($_POST['food']),
                hadSnack: !empty($_POST['snack']),
                createdAt: $createdAt
            );
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];

            $this->redirectTo($redirectUrl);
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Check-in successful registered.'
        ];

        $this->redirectTo($redirectUrl);
    }

    public function showCheckIns(): void
    {
        $handlers = $this->handlerService->getAllHandlers();
        $checkIns = $this->checkInService->getAllCheckIns(order: 'DESC', limit: 10);
        $checkInOptions = CheckInOptions::forForm($this->translationService);
        $htmlLang = $this->translationService->getHtmlLang();
        $flash = $this->pullFlashFromSession();

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        $checkInOptionStats = [];
        $checkInOptionStats[] = [
            'label' => $this->translationService->translate('Peed'),
            'total' => $this->checkInService->checkinRepository->findTotalsByCheckInOption('peed')[0]['total'],
            'colors' => 'bg-dairy-cream-200 border-dairy-cream-200/50'
        ];
        $checkInOptionStats[] = [
            'label' => $this->translationService->translate('Pooped'),
            'total' => $this->checkInService->checkinRepository->findTotalsByCheckInOption('pooped')[0]['total'],
            'colors' => 'bg-opal-800 border-opal-800/50'
        ];
        $checkInOptionStats[] = [
            'label' => $this->translationService->translate('Snacks'),
            'total' => $this->checkInService->checkinRepository->findTotalsByCheckInOption('snack')[0]['total'],
            'colors' => 'bg-periwinkle-800 border-periwinkle-800/50'
        ];

        echo $this->viewRenderer->render(
            'check-ins/index',
            [
                'handlers' => $handlers,
                'checkIns' => $checkIns,
                'checkInOptions' => $checkInOptions,
                'htmlLang' => $htmlLang,
                'viewRenderer' => $this->viewRenderer,
                'handlerService' => $this->handlerService,
                'topHandlers' => $this->checkInService->getTopHandlers(limit:5),
                'flash' => $flash,
                'checkInOptionStats' => $checkInOptionStats,
                'totalCheckIns' => count($this->checkInService->getAllCheckIns(order: 'DESC'))
            ]
        );
    }

    private function pullFlashFromSession(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return is_array($flash) ? $flash : null;
    }

    private function redirectTo(string $url): void
    {
        header("Location: $url");
        exit;
    }
}

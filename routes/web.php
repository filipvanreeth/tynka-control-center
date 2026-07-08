<?php

declare(strict_types=1);

use FastRoute\RouteCollector;
use TynkaControlCenter\CheckIn\Presentation\CheckInController;

/*
 * Web-routes (HTML). De handler is een [controller-klasse, methode]-referentie;
 * de front controller resolvet de klasse via de container en roept de methode
 * aan. Zo is er geen centrale match() meer die logische namen op methodes mapt.
 */
return static function (RouteCollector $routeCollector): void {
    $routeCollector->addRoute('POST', '/checkin', [CheckInController::class, 'handleCheckInSubmission']);
    $routeCollector->addRoute('GET', '/', [CheckInController::class, 'index']);
    $routeCollector->addRoute('GET', '/checkin/{id}/edit', [CheckInController::class, 'edit']);
};

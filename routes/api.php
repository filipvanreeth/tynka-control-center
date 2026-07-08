<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

/*
 * API-routes (JSON) onder de prefix /api/v1. Nog leeg — wordt gevuld in Fase 4/5
 * zodra de Api-controllers bestaan. Versie nu al in de prefix: een API-oppervlak
 * is een contract, versionering achteraf toevoegen is pijnlijk.
 *
 * Zelfde conventie als web: handler = [controller-klasse, methode].
 * Voorbeeld (later):
 *   $routeCollector->addRoute('GET', '/api/v1/checkins', [GetCheckInsController::class, '__invoke']);
 */
return static function (RouteCollector $routeCollector): void {
};

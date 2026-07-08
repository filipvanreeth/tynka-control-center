<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

/*
 * Aggregeert de route-tabellen per kanaal. Elk kanaal-bestand retourneert een
 * closure die zijn routes op de collector registreert. Zo blijft de front
 * controller onwetend van de kanaal-indeling: hij laadt enkel deze ene tabel.
 */
return static function (RouteCollector $routeCollector): void {
    $registerWebRoutes = require dirname(__DIR__) . '/routes/web.php';
    $registerWebRoutes($routeCollector);

    $registerApiRoutes = require dirname(__DIR__) . '/routes/api.php';
    $registerApiRoutes($routeCollector);
};

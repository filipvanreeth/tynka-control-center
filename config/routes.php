<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

/*
 * Routetabel. Handlers zijn logische namen; de front controller mapt ze
 * naar controller-methodes na dispatch.
 */
return static function (RouteCollector $routeCollector): void {
    $routeCollector->addRoute('POST', '/checkin', 'check-in.submit');
    $routeCollector->addRoute('GET', '/', 'check-ins.index');
    $routeCollector->addRoute('GET', '/checkin/{id}/edit', 'check-ins.edit');
};

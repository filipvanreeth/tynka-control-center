<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Templating;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use TynkaControlCenter\CheckIn\Application\Query\CheckInActivityCategoryData;
use TynkaControlCenter\CheckIn\Application\Query\CheckInActivityData;
use TynkaControlCenter\CheckIn\Application\Query\CheckInData;
use TynkaControlCenter\CheckIn\Presentation\CheckInFormView;
use TynkaControlCenter\Handler\Application\Query\HandlerData;
use TynkaControlCenter\Handler\Application\Query\TopHandlerData;
use TynkaControlCenter\Infrastructure\Templating\TwigTemplateEngine;

/**
 * Rendert het volledige dashboard door Twig met echte DTO-vormen (form, cards,
 * top-handlers, stat). Bewijst dat de includes, blocks en property-toegang kloppen.
 */
final class TwigDashboardRenderTest extends TestCase
{
    public function testRendersTheFullDashboard(): void
    {
        $handler = new HandlerData('Filip', 'filip', 'abstract-avatar-01.jpg');
        $walking = new CheckInActivityCategoryData('Walking', 'walking');
        $food = new CheckInActivityCategoryData('Food', 'food');
        $peed = new CheckInActivityData('Peed', 'peed', $walking);
        $snack = new CheckInActivityData('Snack', 'snack', $food);

        $html = $this->engine()->render('check-ins/index', [
            'form' => new CheckInFormView('/checkin', [$handler], [$peed, $snack], null),
            'checkIns' => [new CheckInData('uuid-1', $handler, [$peed], '2020-01-15 08:00')],
            'checkInActivityStats' => [['total' => 3, 'label' => 'Peed', 'colors' => 'border-opal-800']],
            'topHandlers' => [new TopHandlerData($handler, 5)],
            'totalCheckIns' => 1,
            'flash' => null,
        ]);

        self::assertStringContainsString('<html lang="en">', $html);
        self::assertStringContainsString('Filip', $html);
        self::assertStringContainsString('15 January 2020', $html);
        self::assertStringContainsString('Total check-ins: 1', $html);
        self::assertStringContainsString('Peed', $html);
    }

    private function engine(): TwigTemplateEngine
    {
        $twig = new Environment(
            new FilesystemLoader(dirname(__DIR__, 3) . '/resources/views'),
            ['strict_variables' => true],
        );
        $twig->addGlobal('appUrl', 'https://app.test');
        $twig->addGlobal('appVersion', '0.1.9');
        $twig->addGlobal('app_locale', 'en');

        return new TwigTemplateEngine($twig);
    }
}

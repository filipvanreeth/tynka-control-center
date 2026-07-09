<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Templating;

use PHPUnit\Framework\TestCase;
use TynkaControlCenter\CheckIn\Application\Query\CheckInActivityCategoryData;
use TynkaControlCenter\CheckIn\Application\Query\CheckInActivityData;
use TynkaControlCenter\CheckIn\Application\Query\CheckInData;
use TynkaControlCenter\CheckIn\Presentation\CheckInCardData;
use TynkaControlCenter\CheckIn\Presentation\CheckInFormView;
use TynkaControlCenter\CheckIn\Presentation\CheckInIndexView;
use TynkaControlCenter\Common\Infrastructure\FileTranslator;
use TynkaControlCenter\User\Application\Query\UserData;
use TynkaControlCenter\User\Application\Query\TopUserData;
use TynkaControlCenter\Infrastructure\Templating\TwigEnvironmentFactory;
use TynkaControlCenter\Infrastructure\Templating\TwigTemplateEngine;

/**
 * Rendert het volledige dashboard door Twig met echte DTO-vormen (form, cards,
 * top-handlers, stat). Bewijst dat de includes, blocks en property-toegang kloppen.
 */
final class TwigDashboardRenderTest extends TestCase
{
    public function testRendersTheFullDashboard(): void
    {
        $handler = new UserData('Filip', 'filip', 'abstract-avatar-01.jpg');
        $walking = new CheckInActivityCategoryData('Walking', 'walking');
        $food = new CheckInActivityCategoryData('Food', 'food');
        $peed = new CheckInActivityData('Peed', 'peed', $walking);
        $snack = new CheckInActivityData('Snack', 'snack', $food);

        $card = CheckInCardData::fromData(
            new CheckInData('uuid-1', $handler, [$peed], '2020-01-15 08:00'),
            todayLabel: 'Today',
            yesterdayLabel: 'Yesterday',
        );

        $view = new CheckInIndexView(
            form: new CheckInFormView('/checkin', [$handler], [$peed, $snack], null),
            checkIns: [$card],
            checkInActivityStats: [['total' => 3, 'label' => 'Peed', 'colors' => 'border-opal-800']],
            topUsers: [new TopUserData($handler, 5)],
            totalCheckIns: 1,
            flash: null,
        );

        $html = $this->engine()->render('check-ins/index', ['view' => $view]);

        self::assertStringContainsString('<html lang="en">', $html);
        self::assertStringContainsString('Filip', $html);
        self::assertStringContainsString('15 January 2020', $html);
        self::assertStringContainsString('Add Check-In', $html);
        self::assertStringContainsString('Total check-ins: 1', $html);
        self::assertStringContainsString('Peed', $html);
    }

    private function engine(): TwigTemplateEngine
    {
        $twig = TwigEnvironmentFactory::create(
            dirname(__DIR__, 3) . '/resources/views',
            new FileTranslator(dirname(__DIR__, 3) . '/resources/lang'),
            appUrl: 'https://app.test',
            appVersion: '0.1.9',
            locale: 'en',
            options: ['strict_variables' => true],
        );

        return new TwigTemplateEngine($twig);
    }
}

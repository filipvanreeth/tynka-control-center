<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Templating;

use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Common\Infrastructure\FileTranslator;
use TynkaControlCenter\Infrastructure\Templating\TwigEnvironmentFactory;
use TynkaControlCenter\Infrastructure\Templating\TwigTemplateEngine;

final class TwigTemplateEngineTest extends TestCase
{
    public function testRendersATemplateThroughTheBaseLayout(): void
    {
        $html = $this->engine()->render('auth/login', ['flash' => null]);

        self::assertStringContainsString('<html lang="en">', $html);
        self::assertStringContainsString('action="https://app.test/login"', $html);
    }

    public function testAutoEscapesData(): void
    {
        $html = $this->engine()->render('auth/login', [
            'flash' => ['type' => 'error', 'message' => '<script>alert(1)</script>'],
        ]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    private function engine(): TwigTemplateEngine
    {
        $twig = TwigEnvironmentFactory::create(
            dirname(__DIR__, 3) . '/resources/views',
            new FileTranslator(dirname(__DIR__, 3) . '/resources/lang'),
            appUrl: 'https://app.test',
            appVersion: '0',
            locale: 'en',
            options: ['strict_variables' => true],
        );

        return new TwigTemplateEngine($twig);
    }
}

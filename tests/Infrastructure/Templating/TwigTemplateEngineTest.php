<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Templating;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
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
        $twig = new Environment(
            new FilesystemLoader(dirname(__DIR__, 3) . '/resources/views'),
            ['strict_variables' => true],
        );
        $twig->addGlobal('appUrl', 'https://app.test');
        $twig->addGlobal('appVersion', '0');
        $twig->addGlobal('app_locale', 'en');

        return new TwigTemplateEngine($twig);
    }
}

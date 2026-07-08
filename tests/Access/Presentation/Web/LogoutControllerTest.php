<?php

declare(strict_types=1);

namespace App\Tests\Access\Presentation\Web;

use App\Tests\Support\InMemorySession;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use TynkaControlCenter\Access\Presentation\Web\LogoutController;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;

final class LogoutControllerTest extends TestCase
{
    public function testClearsTheSessionAndRedirectsToLogin(): void
    {
        $session = new InMemorySession();
        $session->set('account_id', 'abc-123');

        $response = ($this->controller($session))(new ServerRequest('POST', '/logout'));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/login', $response->getHeaderLine('Location'));
        self::assertNull($session->get('account_id'));
    }

    private function controller(InMemorySession $session): LogoutController
    {
        return new LogoutController($session, $this->translator(), $this->appConfig());
    }

    private function translator(): Translator
    {
        return new class implements Translator {
            public function translate(string $key, ?string $locale = null): string
            {
                return $key;
            }
        };
    }

    private function appConfig(): AppConfig
    {
        return new AppConfig(
            appUrl: 'https://app.test',
            appVersion: '0',
            dbDriver: 'sqlite',
            dbDatabase: ':memory:',
        );
    }
}

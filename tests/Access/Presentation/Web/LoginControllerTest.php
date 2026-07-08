<?php

declare(strict_types=1);

namespace App\Tests\Access\Presentation\Web;

use App\Tests\Support\InMemoryAccountRepository;
use App\Tests\Support\InMemorySession;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\Access\Application\AuthenticateUser;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Domain\Email;
use TynkaControlCenter\Access\Domain\PasswordHash;
use TynkaControlCenter\Access\Domain\Role;
use TynkaControlCenter\Access\Presentation\Http\AuthenticationMiddleware;
use TynkaControlCenter\Access\Presentation\Web\LoginController;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Handler\Domain\HandlerId;
use TynkaControlCenter\Infrastructure\Templating\TemplateEngine;

final class LoginControllerTest extends TestCase
{
    public function testSuccessfulLoginStartsASessionAndRedirectsHome(): void
    {
        $accounts = new InMemoryAccountRepository();
        $account = $this->seededAccount($accounts);
        $session = new InMemorySession();

        $response = $this->controller($accounts, $session)->login(
            $this->postRequest(['email' => 'jan@tynka.be', 'password' => 's3cret']),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertStringEndsWith('/', $response->getHeaderLine('Location'));
        self::assertSame($account->id()->toString(), $session->get('account_id'));
    }

    public function testFailedLoginFlashesAnErrorAndReturnsToLogin(): void
    {
        $accounts = new InMemoryAccountRepository();
        $this->seededAccount($accounts);
        $session = new InMemorySession();

        $response = $this->controller($accounts, $session)->login(
            $this->postRequest(['email' => 'jan@tynka.be', 'password' => 'wrong']),
        );

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('/login', $response->getHeaderLine('Location'));
        self::assertNull($session->get('account_id'));

        $flash = $session->pullFlash();
        self::assertNotNull($flash);
        self::assertSame('error', $flash['type']);
    }

    public function testShowFormRendersForAnAnonymousVisitor(): void
    {
        $response = $this->controller(new InMemoryAccountRepository(), new InMemorySession())
            ->showForm(new ServerRequest('GET', '/login'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('login-form', (string) $response->getBody());
    }

    public function testShowFormRedirectsAnAlreadyLoggedInUser(): void
    {
        $request = (new ServerRequest('GET', '/login'))
            ->withAttribute(AuthenticationMiddleware::ATTRIBUTE, $this->seededAccount(new InMemoryAccountRepository()));

        $response = $this->controller(new InMemoryAccountRepository(), new InMemorySession())->showForm($request);

        self::assertSame(302, $response->getStatusCode());
    }

    private function controller(InMemoryAccountRepository $accounts, InMemorySession $session): LoginController
    {
        return new LoginController(
            new AuthenticateUser($accounts),
            $session,
            $this->templateEngine(),
            $this->translator(),
            $this->appConfig(),
        );
    }

    /**
     * @param array<string, string> $body
     */
    private function postRequest(array $body): ServerRequestInterface
    {
        return (new ServerRequest('POST', '/login'))->withParsedBody($body);
    }

    private function seededAccount(InMemoryAccountRepository $accounts): Account
    {
        $account = Account::register(
            Email::fromString('jan@tynka.be'),
            PasswordHash::fromPlainText('s3cret'),
            Role::Handler,
            HandlerId::fromString('handler-1'),
        );
        $accounts->save($account);

        return $account;
    }

    private function templateEngine(): TemplateEngine
    {
        return new class implements TemplateEngine {
            /**
             * @param array<string, mixed> $data
             */
            public function render(string $template, array $data): string
            {
                return 'login-form:' . $template;
            }
        };
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

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Presentation\Web;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\Access\Application\AuthenticateUser;
use TynkaControlCenter\Access\Domain\Account;
use TynkaControlCenter\Access\Presentation\Http\AuthenticationMiddleware;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Infrastructure\Http\Session;
use TynkaControlCenter\Infrastructure\Templating\TemplateEngine;

final class LoginController
{
    public function __construct(
        private readonly AuthenticateUser $authenticateUser,
        private readonly Session $session,
        private readonly TemplateEngine $viewRenderer,
        private readonly Translator $translator,
        private readonly AppConfig $appConfig,
    ) {
    }

    public function showForm(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getAttribute(AuthenticationMiddleware::ATTRIBUTE) instanceof Account) {
            return new Response(302, ['Location' => $this->appConfig->appUrl . '/']);
        }

        $html = $this->viewRenderer->render('auth/login', [
            'flash' => $this->session->pullFlash(),
        ]);

        return new Response(200, [], $html);
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $body = (array) $request->getParsedBody();
        $email = \is_string($body['email'] ?? null) ? $body['email'] : '';
        $password = \is_string($body['password'] ?? null) ? $body['password'] : '';

        $account = ($this->authenticateUser)($email, $password);

        if ($account === null) {
            $this->session->flash('error', $this->translator->translate('auth.invalid_credentials', $this->locale()));

            return new Response(302, ['Location' => $this->appConfig->appUrl . '/login']);
        }

        // Sessie-id vernieuwen bij de privilege-wissel (tegen session fixation).
        $this->session->regenerate();
        $this->session->set('account_id', $account->id()->toString());

        return new Response(302, ['Location' => $this->appConfig->appUrl . '/']);
    }

    private function locale(): string
    {
        $locale = $this->session->get('locale');

        return \is_string($locale) ? $locale : 'en';
    }
}

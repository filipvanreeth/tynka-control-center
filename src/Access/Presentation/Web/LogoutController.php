<?php

declare(strict_types=1);

namespace TynkaControlCenter\Access\Presentation\Web;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TynkaControlCenter\Common\Domain\Translator;
use TynkaControlCenter\Config\AppConfig;
use TynkaControlCenter\Infrastructure\Http\Session;

final class LogoutController
{
    public function __construct(
        private readonly Session $session,
        private readonly Translator $translator,
        private readonly AppConfig $appConfig,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->remove('account_id');
        $this->session->regenerate();

        $locale = $this->session->get('locale');
        $this->session->flash('success', $this->translator->translate(
            'auth.signed_out',
            \is_string($locale) ? $locale : 'en',
        ));

        return new Response(302, ['Location' => $this->appConfig->appUrl . '/login']);
    }
}

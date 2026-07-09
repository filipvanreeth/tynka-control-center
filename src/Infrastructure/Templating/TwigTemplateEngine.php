<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Templating;

use Twig\Environment;

/**
 * Twig-adapter voor de {@see TemplateEngine}-poort. Callers geven een logische
 * naam (`check-ins/index`); wij mappen die op het `.html.twig`-bestand. Zo
 * wijzigt geen enkele controller mee bij de overstap van plain PHP naar Twig.
 */
final class TwigTemplateEngine implements TemplateEngine
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        return $this->twig->render("{$template}.html.twig", $data);
    }
}

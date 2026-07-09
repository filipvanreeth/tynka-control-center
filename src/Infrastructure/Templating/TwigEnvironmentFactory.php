<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Templating;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use TynkaControlCenter\Common\Domain\Translator;

final class TwigEnvironmentFactory
{
    /**
     * @param array<string, mixed> $options
     */
    public static function create(
        string $viewsPath,
        Translator $translator,
        string $appUrl,
        string $appVersion,
        string $locale,
        array $options = [],
    ): Environment {
        $twig = new Environment(new FilesystemLoader($viewsPath), $options);

        $twig->addGlobal('app_url', $appUrl);
        $twig->addGlobal('app_version', $appVersion);
        $twig->addGlobal('app_locale', $locale);

        $twig->addFilter(new TwigFilter(
            't',
            static fn (string $key): string => $translator->translate($key, $locale),
        ));

        return $twig;
    }
}

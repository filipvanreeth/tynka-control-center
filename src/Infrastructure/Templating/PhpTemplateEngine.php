<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Templating;

use TynkaControlCenter\Config\AppConfig;

final readonly class PhpTemplateEngine implements TemplateEngine
{
    /** @var array<string, mixed> */
    private array $globals;

    public function __construct(
        public string $templatePath,
        AppConfig $appConfig,
    ) {
        $this->globals = [
            'appUrl' => $appConfig->appUrl,
            'appVersion' => $appConfig->appVersion,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $path, array $data = []): string
    {
        ob_start();
        extract([...$this->globals, ...$data]);
        include "$this->templatePath/resources/views/{$path}.php";

        $output = ob_get_clean();

        if ($output === false) {
            throw TemplateRenderingFailed::forPath($path);
        }

        return $output;
    }
}

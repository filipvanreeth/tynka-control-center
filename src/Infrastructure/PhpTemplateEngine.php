<?php
declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure;

use TynkaControlCenter\Config\AppConfig;

final readonly class PhpTemplateEngine implements TemplateEngine
{
    private array $globals;

    public function __construct(
        public string $templatePath,
        AppConfig $appConfig,
    ) {
        $this->globals = [
            'appUrl'     => $appConfig->appUrl,
            'appVersion' => $appConfig->appVersion,
        ];
    }

    /**
     * @param string $path
     * @param array<string, mixed> $data
     * @return string
     */
    public function render(string $path, array $data = []): string
    {
        ob_start();
        extract([...$this->globals, ...$data]);
        include "$this->templatePath/resources/views/{$path}.php";

        return ob_get_clean();
    }
}
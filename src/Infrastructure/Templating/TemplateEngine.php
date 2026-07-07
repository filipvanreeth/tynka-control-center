<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Templating;

interface TemplateEngine
{
    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data): string;
}

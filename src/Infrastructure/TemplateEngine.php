<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure;

interface TemplateEngine
{
    public function render(string $template, array $data): string;
}
<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Templating;

use RuntimeException;
use function sprintf;

final class TemplateRenderingFailed extends RuntimeException
{
    public static function forPath(string $path): self
    {
        return new self(sprintf("Rendering template '%s' produced no output buffer.", $path));
    }
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

final class Avatar
{
    public function __construct(
        private string $path,
    ) {
        if (trim($path) === '') {
            throw new \InvalidArgumentException('Avatar path cannot be empty');
        }
    }

    public function path(): string
    {
        return $this->path;
    }
}
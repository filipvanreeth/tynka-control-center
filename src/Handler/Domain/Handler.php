<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Domain;

use TynkaControlCenter\Common\Domain\Avatar;
use TynkaControlCenter\Common\Domain\Slug;

final class Handler
{
    public function __construct(
        private string $name,
        private Slug $slug,
        private ?Avatar $avatar,
    ) {
    }
    
    public function name(): string
    {
        return $this->name;
    }
    
    public function slug(): Slug
    {
        return $this->slug;
    }
    
    public function avatar(): ?Avatar
    {
        return $this->avatar;
    }
}
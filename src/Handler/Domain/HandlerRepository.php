<?php

declare(strict_types= 1);

namespace TynkaControlCenter\Handler\Domain;

use TynkaControlCenter\Common\Domain\Slug;

interface HandlerRepository
{
    /**
     * @return list<Handler>
     */
    public function find(): array;

    public function findBySlug(Slug $slug): Handler;
}
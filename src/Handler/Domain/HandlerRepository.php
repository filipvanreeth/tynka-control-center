<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Domain;

interface HandlerRepository
{
    /**
     * @return list<Handler>
     */
    public function findAll(): array;

    public function byId(HandlerId $id): ?Handler;
}

<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}

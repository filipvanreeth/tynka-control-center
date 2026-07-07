<?php

declare(strict_types=1);

namespace TynkaControlCenter\Common\Domain;

abstract class AggregateRoot
{
    /** @var DomainEvent[] */
    private array $recordedEvents = [];

    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /** @return DomainEvent[] */
    public function recordedEvents(): array
    {
        return $this->recordedEvents;
    }

    public function clearEvents(): void
    {
        $this->recordedEvents = [];
    }
}

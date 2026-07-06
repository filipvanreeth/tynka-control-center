<?php
declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Domain;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class CheckIn
{
    public function __construct(
        public ?int $id,
        public readonly string $uuid,
        public readonly string $handler,
        public readonly bool $hasPeed,
        public readonly bool $hasPooped,
        public readonly bool $hadFood,
        public readonly bool $hadSnack,
        private array $options,
        public readonly DateTimeImmutable $createdAt
    ) {
    }
    
    public static function create(
        string $handler,
        bool $hasPeed,
        bool $hasPooped,
        bool $hadFood,
        bool $hadSnack,
        array $options,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            id: null,
            uuid: Uuid::uuid7()->toString(),
            handler: $handler,
            hasPeed: $hasPeed,
            hasPooped: $hasPooped,
            hadFood: $hadFood,
            hadSnack: $hadSnack,
            options: $options,
            createdAt: $createdAt
        );
    }
    
    public function id(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    public function uuid(): string
    {
        return $this->uuid;
    }
    
    public function handler(): string
    {
        return $this->handler;
    }
    
    public function hasPeed(): bool
    {
        return $this->hasPeed;
    }
    
    public function hasPooped(): bool
    {
        return $this->hasPooped;
    }
    
    public function hadFood(): bool
    {
        return $this->hadFood;
    }
    
    public function hadSnack(): bool
    {
        return $this->hadSnack;
    }
    
    public function options(): array
    {
        return $this->options;
    }
    
    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
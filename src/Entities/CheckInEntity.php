<?php
declare(strict_types=1);

namespace TynkaControlCenter\Entities;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class CheckInEntity
{
    public function __construct(
        private ?int $id,
        public readonly string $uuid,
        public readonly string $handler,
        public readonly bool $hasPeed,
        public readonly bool $hasPooped,
        public readonly bool $hadFood,
        public readonly bool $hadSnack,
        public readonly DateTimeImmutable $createdAt
    ) {
    }
    
    public static function create(
        string $handler,
        bool $hasPeed,
        bool $hasPooped,
        bool $hadFood,
        bool $hadSnack,
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
            createdAt: $createdAt
        );
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    
    public function getUuid(): string
    {
        return $this->uuid;
    }
    
    public function getHandler(): string
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
    
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
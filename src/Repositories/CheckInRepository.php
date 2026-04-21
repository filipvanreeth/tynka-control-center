<?php
declare(strict_types=1);

namespace TynkaControlCenter\Repositories;

use DateTimeImmutable;
use PDO;
use TynkaControlCenter\Entities\CheckInEntity;

class CheckInRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $tableName
    ) {
    }

    public function storeCheckIn(CheckInEntity $checkIn): CheckInEntity
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->tableName} (uuid, handler, peed, pooped, food, snack, created_at) VALUES (:uuid, :handler, :peed, :pooped, :food, :snack, :created_at)"
        );

        $data = [
            ':uuid' => $checkIn->getUuid(),
            ':handler' => $checkIn->getHandler(),
            ':peed' => $checkIn->hasPeed() ? 1 : 0,
            ':pooped' => $checkIn->hasPooped() ? 1 : 0,
            ':food' => $checkIn->hadFood() ? 1 : 0,
            ':snack' => $checkIn->hadSnack() ? 1 : 0,
            ':created_at' => $checkIn->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        $executed = $stmt->execute($data);

        if (!$executed) {
            throw new \Exception('Failed to store check-in: ' . implode(', ', $stmt->errorInfo()));
        }

        $id = $this->pdo->lastInsertId();
        $checkIn->setId((int) $id);

        return $checkIn;
    }

    public function findCheckInById(string $uuid): ?CheckInEntity
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM {$this->tableName} WHERE id = :id"
        );

        $stmt->execute([':id' => $uuid]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new CheckInEntity(
            id: $row['id'],
            uuid: $row['uuid'],
            handler: $row['handler'],
            hasPeed: (bool) $row['peed'],
            hasPooped: (bool) $row['pooped'],
            hadFood: (bool) $row['food'],
            hadSnack: (bool) $row['snack'],
            createdAt: new DateTimeImmutable($row['created_at'])
        );
    }

    public function findAll(string $order = 'DESC', ?int $limit = null): array
    {
        $limit ??= -1;
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = $this->pdo->query("SELECT * FROM {$this->tableName} ORDER BY created_at {$order} LIMIT {$limit}");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            return [];
        }

        return array_map(fn($row) => new CheckInEntity(
            id: $row['id'],
            uuid: $row['uuid'],
            handler: $row['handler'],
            hasPeed: (bool) $row['peed'],
            hasPooped: (bool) $row['pooped'],
            hadFood: (bool) $row['food'],
            hadSnack: (bool) $row['snack'],
            createdAt: new DateTimeImmutable($row['created_at'])
        ), $rows);
    }

    public function findLatestCheckIn(): ?CheckInEntity
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->tableName} ORDER BY created_at DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new CheckInEntity(
            id: $row['id'],
            uuid: $row['uuid'],
            handler: $row['handler'],
            hasPeed: (bool) $row['peed'],
            hasPooped: (bool) $row['pooped'],
            hadFood: (bool) $row['food'],
            hadSnack: (bool) $row['snack'],
            createdAt: new DateTimeImmutable($row['created_at'])
        );
    }

    public function findTopHandlers(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT handler, COUNT(*) as total
         FROM {$this->tableName}
         GROUP BY handler
         ORDER BY total DESC
         LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findTotalsByCheckInOption(string $option): array
    {
        $validOptions = ['peed', 'pooped', 'food', 'snack'];
        if (!in_array($option, $validOptions)) {
            throw new \InvalidArgumentException('Invalid check-in option: ' . $option);
        }

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as total
             FROM {$this->tableName}
             WHERE {$option} = 1
             ORDER BY total DESC"
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure\Persistence;

use PDO;
use TynkaControlCenter\CheckIn\Application\Query\CheckInReadModel;

final class PdoCheckInReadModel implements CheckInReadModel
{
    private const ACTIVITY_COLUMNS = ['peed', 'pooped', 'food', 'snack'];

    public function __construct(
        private readonly PDO $pdo,
        private readonly string $tableName,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM {$this->tableName} ORDER BY created_at DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array{
     *     uuid: string,
     *     handler: string,
     *     peed: bool,
     *     pooped: bool,
     *     food: bool,
     *     snack: bool,
     *     created_at: string,
     * }|null
     */
    public function byId(string $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tableName} WHERE uuid = :uuid"
        );
        $stmt->execute([':uuid' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        $uuid = $row['uuid'] ?? null;
        $handler = $row['handler'] ?? null;
        $createdAt = $row['created_at'] ?? null;

        if (!is_string($uuid) || !is_string($handler) || !is_string($createdAt)) {
            throw new \UnexpectedValueException('Malformed check-in row: missing string fields.');
        }

        return [
            'uuid' => $uuid,
            'handler' => $handler,
            'peed' => (bool) ($row['peed'] ?? false),
            'pooped' => (bool) ($row['pooped'] ?? false),
            'food' => (bool) ($row['food'] ?? false),
            'snack' => (bool) ($row['snack'] ?? false),
            'created_at' => $createdAt,
        ];
    }

    public function totalForActivity(string $activity): int
    {
        if (!in_array($activity, self::ACTIVITY_COLUMNS, true)) {
            throw new \InvalidArgumentException('Invalid check-in activity: ' . $activity);
        }

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->tableName} WHERE {$activity} = 1"
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return list<array{handler: string, total: int}>
     */
    public function handlerCounts(int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT handler, COUNT(*) AS total
             FROM {$this->tableName}
             GROUP BY handler
             ORDER BY total DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            static fn(array $row): array => [
                'handler' => (string) $row['handler'],
                'total' => (int) $row['total'],
            ],
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }
}

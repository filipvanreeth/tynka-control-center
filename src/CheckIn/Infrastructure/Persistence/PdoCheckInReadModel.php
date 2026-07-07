<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Infrastructure\Persistence;

use PDO;
use TynkaControlCenter\CheckIn\Application\Query\CheckInReadModel;

final class PdoCheckInReadModel implements CheckInReadModel
{
    private const OPTION_COLUMNS = ['peed', 'pooped', 'food', 'snack'];

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

    public function totalForOption(string $option): int
    {
        if (!in_array($option, self::OPTION_COLUMNS, true)) {
            throw new \InvalidArgumentException('Invalid check-in option: ' . $option);
        }

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->tableName} WHERE {$option} = 1"
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

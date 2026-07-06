<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Handler\Application\Query\HandlerData;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final class GetTopHandlersHandler
{
    public function __construct(
        public readonly \PDO $pdo,
        public readonly HandlerRepository $handlerRepository
    ) {
    }

    public function handle(): array
    {
        $limit = 5;

        $stmt = $this->pdo->prepare(
            "SELECT handler, COUNT(*) as total
         FROM checkins
         GROUP BY handler
         ORDER BY total DESC
         LIMIT :limit"
        );
        $stmt->bindValue(
            ':limit',
            $limit,
            \PDO::PARAM_INT
        );
        $stmt->execute();

        $handlers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $rows = array_map(
            function (array $row) {
                $handler = (new GetHandlerBySlugHandler(
                    $this->handlerRepository
                ))->handle(new Slug($row['handler']));
                return $handler;
            },
            $handlers
        );

        return $rows;
    }
}
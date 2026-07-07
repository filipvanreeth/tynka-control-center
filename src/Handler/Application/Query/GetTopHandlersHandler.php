<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

use TynkaControlCenter\CheckIn\Application\Query\CheckInReadModel;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final class GetTopHandlersHandler
{
    public function __construct(
        private readonly CheckInReadModel $readModel,
        private readonly HandlerRepository $handlerRepository
    ) {
    }

    /**
     * @return list<TopHandlerData>
     */
    public function handle(): array
    {
        $handlers = $this->readModel->handlerCounts(5);
        $getHandlerById = new GetHandlerByIdHandler($this->handlerRepository);

        return array_map(
            function (array $row) use ($getHandlerById): TopHandlerData {
                $handler = $getHandlerById->handle(
                    new GetHandlerByIdQuery($row['handler'])
                );

                return new TopHandlerData(
                    handler: $handler ?? new HandlerData(
                        name: $row['handler'],
                        id: $row['handler'],
                        avatar: null,
                    ),
                    total: (int) $row['total'],
                );
            },
            $handlers
        );
    }
}

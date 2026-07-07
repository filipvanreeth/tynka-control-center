<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

use TynkaControlCenter\Handler\Domain\Handler;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final readonly class GetAllHandlersHandler
{
    public function __construct(
        public readonly HandlerRepository $handlerRepository
    )
    {
    }

    /**
     * @return list<HandlerData>
     */
    public function handle(): array
    {
        return array_map(
            function (Handler $handler): HandlerData {
                return HandlerData::fromDomain($handler);
            },
            $this->handlerRepository->findAll()
        );
    }
}

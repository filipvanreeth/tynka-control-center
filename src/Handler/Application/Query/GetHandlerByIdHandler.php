<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

use TynkaControlCenter\Handler\Domain\HandlerId;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final class GetHandlerByIdHandler
{
    public function __construct(
        private readonly HandlerRepository $handlerRepository,
    ) {
    }

    public function handle(GetHandlerByIdQuery $query): ?HandlerData
    {
        $handler = $this->handlerRepository->byId(
            HandlerId::fromString($query->id)
        );

        return $handler !== null ? HandlerData::fromDomain($handler) : null;
    }
}

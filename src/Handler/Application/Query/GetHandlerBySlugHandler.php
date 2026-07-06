<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Application\Query;

use TynkaControlCenter\Common\Domain\Slug;
use TynkaControlCenter\Handler\Domain\HandlerRepository;
use TynkaControlCenter\Handler\Application\Query\HandlerData;

final class GetHandlerBySlugHandler
{
    public function __construct(
        public readonly HandlerRepository $handlerRepository,
    ) {}

    public function handle(Slug $slug): ?HandlerData
    {
        $handlers = $this->handlerRepository->find();

        if (empty($handlers)) {
            return null;
        }

        foreach ($handlers as $handler) {
            if ($slug->value() === $handler->slug()->value()) {
                return new HandlerData(
                    name: $handler->name(),
                    slug: $handler->slug()->value(),
                    avatar: $handler->avatar()?->path(),
                );
            }
        }

        return null;
    }
}

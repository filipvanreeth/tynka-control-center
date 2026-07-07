<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Infrastructure;

use TynkaControlCenter\Common\Domain\Avatar;
use TynkaControlCenter\Handler\Domain\Handler;
use TynkaControlCenter\Handler\Domain\HandlerId;
use TynkaControlCenter\Handler\Domain\HandlerRepository;

final class InMemoryHandlerRepository implements HandlerRepository
{
    public function byId(HandlerId $id): ?Handler
    {
        foreach ($this->findAll() as $handler) {
            if ($handler->id()->equals($id)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @return list<Handler>
     */
    public function findAll(): array
    {
        return [
            Handler::reconstitute(
                HandlerId::fromString('filip'),
                'Filip',
                new Avatar('abstract-avatar-01.jpg'),
            ),
            Handler::reconstitute(
                HandlerId::fromString('nathalie'),
                'Nathalie',
                new Avatar('abstract-avatar-02.jpg'),
            ),
            Handler::reconstitute(
                HandlerId::fromString('maya'),
                'Maya',
                new Avatar('abstract-avatar-03.jpg'),
            ),
            Handler::reconstitute(
                HandlerId::fromString('eline'),
                'Eline',
                new Avatar('abstract-avatar-04.jpg'),
            ),
            Handler::reconstitute(
                HandlerId::fromString('xander'),
                'Xander',
                new Avatar('abstract-avatar-05.jpg'),
            ),
            Handler::reconstitute(
                HandlerId::fromString('dog-sitter'),
                'Dog Sitter',
                new Avatar('abstract-avatar-05.jpg'),
            ),
        ];
    }
}

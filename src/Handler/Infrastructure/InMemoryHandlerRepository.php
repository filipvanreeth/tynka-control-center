<?php

declare(strict_types=1);

namespace TynkaControlCenter\Handler\Infrastructure;

use TynkaControlCenter\Common\Domain\Avatar;
use TynkaControlCenter\Handler\Domain\Handler;
use TynkaControlCenter\Handler\Domain\HandlerRepository;
use TynkaControlCenter\Common\Domain\Slug;

final class InMemoryHandlerRepository implements HandlerRepository
{
    /**
     * @return Handler
     */
    public function findBySlug(Slug $slug): Handler
    {
        foreach ($this->find() as $handler) {
            if ($handler->slug()->value() === $slug->value()) {
                return $handler;
            }
        }

        throw new \RuntimeException("Handler '{$slug->value()}' not found.");
    }

    public function find(): array
    {
        return [
            new Handler(
                name: 'Filip',
                slug: new Slug('filip'),
                avatar: new Avatar('abstract-avatar-01.jpg'),
            ),
            new Handler(
                name: 'Nathalie',
                slug: new Slug('nathalie'),
                avatar: new Avatar('abstract-avatar-02.jpg'),
            ),
            new Handler(
                name: 'Maya',
                slug: new Slug('maya'),
                avatar: new Avatar('abstract-avatar-03.jpg'),
            ),
            new Handler(
                name: 'Eline',
                slug: new Slug('eline'),
                avatar: new Avatar('abstract-avatar-04.jpg'),
            ),
            new Handler(
                name: 'Xander',
                slug: new Slug('xander'),
                avatar: new Avatar('abstract-avatar-05.jpg'),
            ),
            new Handler(
                name: 'Dog Sitter',
                slug: new Slug('dog-sitter'),
                avatar: new Avatar('abstract-avatar-05.jpg'),
            ),
        ];
    }
}
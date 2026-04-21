<?php
declare(strict_types=1);

namespace TynkaControlCenter\Services;

class HandlerService
{
    public function getAllHandlers(): array
    {
        return [
            [
                'name' => 'Filip',
                'value' => 'filip',
                'avatar' => 'abstract-avatar-01.jpg'
            ],
            [
                'name' => 'Nathalie',
                'value' => 'nathalie',
                'avatar' => 'abstract-avatar-02.jpg'
            ],
            [
                'name' => 'Eline',
                'value' => 'eline',
                'avatar' => 'abstract-avatar-03.jpg'
            ],
            [
                'name' => 'Maya',
                'value' => 'maya',
                'avatar' => 'abstract-avatar-04.jpg'
            ],
            [
                'name' => 'Xander',
                'value' => 'xander',
                'avatar' => 'abstract-avatar-05.jpg'
            ],
            [
                'name' => 'Dog Sitter',
                'value' => 'dog-sitter'
            ]
        ];
    }

    public function getHandler(string $value): ?array
    {
        foreach ($this->getAllHandlers() as $handler) {
            if ($handler['value'] === $value) {
                return $handler;
            }
        }

        return null;
    }

    public function getHandlerName(string $value): ?string
    {
        $handlers = $this->getAllHandlers();

        foreach ($handlers as $handler) {
            if ($handler['value'] === $value) {
                return $handler['name'];
            }
        }

        return null;
    }
}
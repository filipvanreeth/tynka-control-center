<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use TynkaControlCenter\CheckIn\Application\Query\CheckInData;
use TynkaControlCenter\CheckIn\Application\Query\CheckInOptionData;
use TynkaControlCenter\Handler\Application\Query\HandlerData;

final readonly class CheckInFormView
{
    /**
     * @param string $action
     * @param array<HandlerData> $handlers
     * @param array<CheckInOptionData> $options
     * @param ?CheckInData $data
     */
    public function __construct(
        public string $action,
        public array $handlers,
        public array $options,
        public ?CheckInData $data,
    ) {
    }

    public function isOptionSelected(CheckInOptionData $option): bool
    {
        if ($this->data === null) {
            return false;
        }

        return \in_array(
            $option->slug,
            $this->data->selectedOptions,
            true
        );
    }
}
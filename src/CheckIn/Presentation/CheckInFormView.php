<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use TynkaControlCenter\CheckIn\Application\Query\CheckInData;
use TynkaControlCenter\CheckIn\Application\Query\CheckInActivityData;
use TynkaControlCenter\Handler\Application\Query\HandlerData;

final readonly class CheckInFormView
{
    /**
     * @param string $action
     * @param array<HandlerData> $handlers
     * @param array<CheckInActivityData> $activities
     * @param ?CheckInData $data
     */
    public function __construct(
        public string $action,
        public array $handlers,
        public array $activities,
        public ?CheckInData $data,
    ) {
    }

    public function isActivitySelected(CheckInActivityData $activity): bool
    {
        if ($this->data === null) {
            return false;
        }

        foreach ($this->data->activities as $selected) {
            if ($selected->id === $activity->id) {
                return true;
            }
        }

        return false;
    }
}
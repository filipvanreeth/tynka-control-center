<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use DateTimeImmutable;
use DateTimeZone;
use TynkaControlCenter\CheckIn\Application\Query\CheckInData;
use TynkaControlCenter\User\Application\Query\UserData;

/**
 * Display-ready view-model voor één check-in-card. Trekt de relatieve-datum-logica
 * ('Today'/'Yesterday' + tijd, of absolute datum) uit het template: die is presentatie,
 * geen data. De labels komen al vertaald binnen (controller → Translator-poort); de
 * VO kent de vertaallaag dus niet, enkel de datumregels.
 */
final readonly class CheckInCardData
{
    /**
     * @param list<string> $activityIds
     */
    private function __construct(
        public string $id,
        public UserData $handler,
        public array $activityIds,
        public string $displayDate,
        public string $dateColor,
    ) {
    }

    public static function fromData(
        CheckInData $data,
        string $todayLabel,
        string $yesterdayLabel,
        string $timezone = 'Europe/Brussels',
    ): self {
        $tz = new DateTimeZone($timezone);
        $created = (new DateTimeImmutable($data->createdAt . ' UTC'))->setTimezone($tz);
        $today = new DateTimeImmutable('now', $tz);

        $day = $created->format('Y-m-d');
        $time = $created->format('H:i');

        if ($day === $today->format('Y-m-d')) {
            $displayDate = "{$todayLabel} - {$time}";
            $dateColor = 'text-opal-500';
        } elseif ($day === $today->modify('-1 day')->format('Y-m-d')) {
            $displayDate = "{$yesterdayLabel} - {$time}";
            $dateColor = 'text-periwinkle-500';
        } else {
            $displayDate = $created->format('j F Y - H:i');
            $dateColor = 'text-gray-500';
        }

        return new self(
            id: $data->id,
            handler: $data->handler,
            activityIds: array_map(static fn ($activity): string => $activity->id, $data->activities),
            displayDate: $displayDate,
            dateColor: $dateColor,
        );
    }
}

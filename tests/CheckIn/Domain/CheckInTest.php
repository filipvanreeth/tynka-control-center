<?php

declare(strict_types=1);

namespace App\Tests\CheckIn\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use TynkaControlCenter\CheckIn\Domain\CheckIn;
use TynkaControlCenter\CheckIn\Domain\CheckInActivityId;
use TynkaControlCenter\CheckIn\Domain\InvalidCheckIn;
use TynkaControlCenter\User\Domain\UserId;

/**
 * Bewaakt de invariant van het aggregaat: een check-in registreert minstens één
 * activiteit. Dit geldt op elk pad naar CheckIn::create(), niet enkel via het form.
 */
final class CheckInTest extends TestCase
{
    public function testCannotBeCreatedWithoutActivities(): void
    {
        $this->expectException(InvalidCheckIn::class);

        CheckIn::create(
            UserId::fromString('1'),
            [],
            new DateTimeImmutable('2026-07-08 10:00'),
        );
    }

    public function testIsCreatedWithAtLeastOneActivity(): void
    {
        $checkIn = CheckIn::create(
            UserId::fromString('1'),
            [CheckInActivityId::fromString('peed')],
            new DateTimeImmutable('2026-07-08 10:00'),
        );

        self::assertCount(1, $checkIn->selectedActivities());
    }
}

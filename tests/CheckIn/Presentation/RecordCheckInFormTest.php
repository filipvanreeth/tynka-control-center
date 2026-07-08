<?php

declare(strict_types=1);

namespace App\Tests\CheckIn\Presentation;

use PHPUnit\Framework\TestCase;
use TynkaControlCenter\CheckIn\Presentation\RecordCheckInForm;

/**
 * Toont de winst van een puur input-object: valideren zonder Translator, locale of
 * webserver. Asserties gaan op stabiele codes, niet op vertaalde tekst. De geldige
 * activiteit-slugs worden ingegeven (bron: repository), niet hardgecodeerd.
 */
final class RecordCheckInFormTest extends TestCase
{
    /** @var list<string> */
    private const KNOWN_ACTIVITY_SLUGS = ['peed', 'pooped', 'food', 'snack'];

    public function testValidInputProducesACommandAndNoErrors(): void
    {
        $form = RecordCheckInForm::fromRequest([
            'handler' => '1',
            'checkInAt' => '2026-07-08T10:00',
            'peed' => '1',
            'food' => '1',
        ], self::KNOWN_ACTIVITY_SLUGS);

        self::assertSame([], $form->errors);
        self::assertNotNull($form->command);
        self::assertSame('1', $form->command->handler);
        self::assertSame(['peed', 'food'], $form->command->selectedActivities);
    }

    public function testMissingHandlerYieldsACodeAndNoCommand(): void
    {
        $form = RecordCheckInForm::fromRequest([
            'checkInAt' => '2026-07-08T10:00',
        ], self::KNOWN_ACTIVITY_SLUGS);

        self::assertNull($form->command);
        self::assertSame('check_in.validation.handler_required', $form->errors['handler'] ?? null);
    }

    public function testInvalidMomentYieldsACode(): void
    {
        $form = RecordCheckInForm::fromRequest([
            'handler' => '1',
            'checkInAt' => 'niet-een-datum',
        ], self::KNOWN_ACTIVITY_SLUGS);

        self::assertNull($form->command);
        self::assertSame('check_in.validation.moment_invalid', $form->errors['checkInAt'] ?? null);
    }

    public function testNotificationPatternCollectsAllErrorsAtOnce(): void
    {
        $form = RecordCheckInForm::fromRequest([], self::KNOWN_ACTIVITY_SLUGS);

        // Beide fouten in één keer — niet stoppen bij de eerste.
        self::assertArrayHasKey('handler', $form->errors);
        self::assertArrayHasKey('checkInAt', $form->errors);
    }

    public function testUnknownActivityFieldsAreIgnored(): void
    {
        // Enkel bekende slugs worden geëxtraheerd: de ingegeven set is een whitelist.
        $form = RecordCheckInForm::fromRequest([
            'handler' => '1',
            'checkInAt' => '2026-07-08T10:00',
            'peed' => '1',
            'evil' => '1',
        ], self::KNOWN_ACTIVITY_SLUGS);

        self::assertNotNull($form->command);
        self::assertSame(['peed'], $form->command->selectedActivities);
    }

    public function testMissingActivitiesYieldsACode(): void
    {
        $form = RecordCheckInForm::fromRequest([
            'handler' => '1',
            'checkInAt' => '2026-07-08T10:00',
        ], self::KNOWN_ACTIVITY_SLUGS);

        self::assertNull($form->command);
        self::assertSame('check_in.validation.activity_required', $form->errors['activities'] ?? null);
    }
}

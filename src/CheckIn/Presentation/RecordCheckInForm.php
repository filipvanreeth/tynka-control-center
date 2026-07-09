<?php

declare(strict_types=1);

namespace TynkaControlCenter\CheckIn\Presentation;

use DateTimeImmutable;
use DateTimeZone;
use TynkaControlCenter\CheckIn\Application\Command\RecordCheckInCommand;

/**
 * Presentatie-input-object voor het check-in-formulier. Valideert de *vorm* van
 * de POST-data (verplichte velden, parseerbaar tijdstip) volgens de
 * Notification-pattern: het verzamelt álle fouten in plaats van bij de eerste te
 * stoppen, zodat de gebruiker ze in één keer ziet.
 *
 * Fouten zijn stabiele vertaal-*codes*, geen mensentaal — het verwoorden gebeurt
 * aan de rand (controller/view) met de Translator. Zo blijft dit object een pure,
 * locale-onafhankelijke functie die triviaal op codes te testen is.
 *
 * Let op de taakverdeling: dit form checkt de *vorm*; de domein-Value-Objects
 * (`UserId`, `CheckInActivityId`) bewaken de *betekenis*. Dupliceer die
 * domeinregels hier niet.
 */
final class RecordCheckInForm
{
    /**
     * @param array<string, string> $errors veld => vertaalcode
     */
    private function __construct(
        public readonly ?RecordCheckInCommand $command,
        public readonly array $errors,
    ) {
    }

    /**
     * @param array<string, mixed> $body de geparste request-body
     * @param list<string> $activityIds geldige activiteit-ids (bron: repository)
     */
    public static function fromRequest(array $body, array $activityIds): self
    {
        $errors = [];

        $handler = trim((string) ($body['handler'] ?? ''));
        if ($handler === '') {
            $errors['handler'] = 'check_in.validation.handler_required';
        }

        $createdAt = DateTimeImmutable::createFromFormat(
            "Y-m-d\TH:i",
            (string) ($body['checkInAt'] ?? ''),
            new DateTimeZone('Europe/Brussels'),
        );
        if ($createdAt === false) {
            $errors['checkInAt'] = 'check_in.validation.moment_invalid';
        }

        $selectedActivities = array_values(array_filter(
            $activityIds,
            static fn(string $id): bool => !empty($body[$id]),
        ));

        // Spiegelt de domein-invariant (CheckIn::create) voor een vriendelijke
        // UX-melding; het domein blijft de autoritaire bewaker.
        if ($selectedActivities === []) {
            $errors['activities'] = 'check_in.validation.activity_required';
        }

        if ($errors !== [] || $createdAt === false) {
            return new self(null, $errors);
        }

        return new self(
            new RecordCheckInCommand(
                handler: $handler,
                selectedActivities: $selectedActivities,
                createdAt: $createdAt->setTimezone(new DateTimeZone('UTC')),
            ),
            [],
        );
    }
}

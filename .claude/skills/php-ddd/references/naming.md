# Naamgeving & ubiquitaire taal

## Ubiquitaire taal

Alle klasse-, methode- en variabelenamen gebruiken domeinterminologie — geen technisch
jargon. De taal is gedeeld met domeinexperts en consistent doorheen de volledige codebase.

- Klasse: `CheckIn`, niet `CheckInRecord`.
- Methode: `complete()`, niet `setCompleted(true)`.
- Event: `CheckInCompleted`, niet `StatusUpdated`.
- Module: `CheckIn/`, `Handler/` — **nooit** `Models/`, `Services/`, `ValueObjects/`.

Modules heten naar domeinconcepten, nooit naar technische patronen. Als een mapnaam een
patroon beschrijft (`Repositories/`, `Entities/`) i.p.v. een concept, klopt de indeling niet.

## Naamgevingstabel

| Wat | Patroon | Voorbeeld |
|---|---|---|
| Entity / Aggregate Root | `{Naam}` | `CheckIn` |
| Identity Value Object | `{Naam}Id` | `CheckInId`, `HandlerId` |
| Overige Value Objects | beschrijvend | `DogName`, `Slug`, `TranslatedText` |
| Repository-interface | `{Naam}Repository` | `CheckInRepository` |
| Repository-implementatie (PDO) | `Pdo{Naam}Repository` | `PdoCheckInRepository` |
| In-memory repository | `InMemory{Naam}Repository` | `InMemoryCheckInRepository` |
| Command | `{Actie}{Naam}Command` | `CompleteCheckInCommand` |
| Command handler | `{Actie}{Naam}Handler` | `CompleteCheckInHandler` |
| Query handler | `Get{Naam(en)}Handler` | `GetAllCheckInsHandler` |
| Read-model / DTO (query-output) | `{Naam}Data` | `CheckInData` |
| View-model (Presentation, optioneel) | `{Naam}View` | `CheckInFormView` |
| Domain Event | `{Naam}{ActieVerleden}` | `CheckInCompleted`, `PostWasCreated` |
| Domain Service | beschrijvend (ubiquitaire taal) | `AvailabilityChecker` |

## Stijlconventies

- `declare(strict_types=1)` in elk bestand.
- `final` op alle klassen tenzij overerving expliciet vereist is (`AggregateRoot`,
  abstracte basisklassen).
- Geen comments tenzij het *waarom* niet uit de code blijkt.
- Named arguments bij meer dan twee parameters of bij constructoraanroepen.
- Geen `var_dump`, `die()` of `echo`-debugging in productiecode.
- Getters zonder `get`-prefix waar dat de ubiquitaire taal dient: `id()`, `name()`,
  `handler()` — niet `getId()`.

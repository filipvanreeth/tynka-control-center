# Tynka Control Center — Claude Code richtlijnen

## Project

PHP-applicatie gebouwd op DDD-principes met CQRS. Geen framework (geen Laravel/Symfony MVC) — enkel losse Symfony-componenten, PHP-DI, Twig en PDO.

Namespace root: `TynkaControlCenter\` → `src/`

## Referentie

> **DDD-boek:** *Domain-Driven Design in PHP* (2nd ed.) — Buenosvinos, Soronellas & Akbary  
> Lokaal: `/Users/filip/Library/Mobile Documents/com~apple~CloudDocs/Books/ddd-in-php.pdf`  
> Implementaties in deze codebase volgen de patronen uit dit boek.

---

## Ubiquitaire Taal

Alle klasse-, methode- en variabelenamen gebruiken domeinterminologie — geen technisch jargon. De taal is gedeeld met domeinexperts en consistent doorheen de volledige codebase.

- Klasse: `CheckIn`, niet `CheckInRecord`
- Methode: `complete()`, niet `setCompleted(true)`
- Event: `CheckInCompleted`, niet `StatusUpdated`
- Module: `CheckIn/`, `Dog/`, niet `Models/`, `Services/`

Modules worden benoemd naar domeinconcepten, **nooit** naar technische patronen (`ValueObjects/`, `Services/`, `Repositories/`).

---

## Architectuur

Fundament: **Hexagonal Architecture** (Ports & Adapters).

- **Port** = interface in de Domain-laag
- **Adapter** = concrete implementatie in de Infrastructure-laag

De Domain-laag kent Infrastructure niet. Alle afhankelijkheden wijzen naar binnen (Domain). Dit garandeert dat domeinlogica testbaar is zonder database of framework.

---

## Lagenindeling

Elke bounded context volgt deze structuur:

```
src/{Context}/
  Domain/
    {AggregateRoot}.php      ← Aggregate roots (erven van AggregateRoot)
    {Entity}.php             ← Interne Entities
    {ValueObject}.php        ← Value Objects
    {Name}Repository.php     ← Repository interfaces (Ports)
    Event/                   ← Domain Events
    Service/                 ← Domain Services (stateless domeinlogica)
  Application/
    Command/                 ← Commands + Handlers (schrijfoperaties)
    Query/                   ← Query Handlers (leesoperaties)
    View/                    ← DTO's voor de presentatielaag
  Infrastructure/
    Persistence/             ← Concrete repository-implementaties (Adapters, PDO)
  Presentation/
    Http/                    ← Web controllers
    Api/                     ← API controllers
    Console/                 ← Console commands
```

**Infrastructure ≠ Presentation.** Infrastructure gaat over technische details (database, externe services). De UI/Presentation-laag staat hier los van.

---

## Aggregaten

Een **Aggregate** is een cluster van domeinobjecten die samen één consistentiegrens vormen. Elke Aggregate heeft precies één **Aggregate Root** — de enige klasse waarmee de buitenwereld mag communiceren.

Regels:
- Externe objecten refereren uitsluitend aan de Aggregate Root, nooit aan interne Entities
- Cross-aggregate referenties verlopen via ID (Value Object), niet via objectreferentie
- Aggregates zijn zo klein mogelijk — enkel wat nodig is om invarianten te bewaken
- Alle invarianten worden afgedwongen door de Aggregate Root

```php
// Juist: cross-aggregate referentie via ID
final class CheckIn extends AggregateRoot
{
    private readonly CheckInId $id;
    private readonly DogId $dogId; // ← ID, niet Dog $dog
}

// Fout: directe objectreferentie over aggregate-grenzen
final class CheckIn extends AggregateRoot
{
    private readonly Dog $dog; // ← nooit
}
```

### AggregateRoot basisklasse

Alle Aggregate Roots erven van `AggregateRoot`. Deze klasse is **niet** `final`.

```php
abstract class AggregateRoot
{
    /** @var DomainEvent[] */
    private array $recordedEvents = [];

    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /** @return DomainEvent[] */
    public function recordedEvents(): array
    {
        return $this->recordedEvents;
    }

    public function clearEvents(): void
    {
        $this->recordedEvents = [];
    }
}
```

---

## Domeinobjecten

### Entities

- Identiteit bepaalt gelijkheid (`id()` methode, niet property-vergelijking)
- Interne Entities zijn enkel toegankelijk via de Aggregate Root
- Rich Domain Model: gedragslogica zit **in** de Entity, niet in een losse service

### Encapsulatie

- Gebruik altijd `private` voor properties in Entities, nooit `public readonly`
- Blootstelling verloopt uitsluitend via expliciete getter-methoden
- `readonly` is enkel toegestaan in combinatie met `private`: `private readonly`

```php
// correct
private readonly CheckInId $id;
public function id(): CheckInId { return $this->id; }

// niet doen
public readonly CheckInId $id;
```

### Identiteit

- De Identity van een Entity is altijd een **Value Object** (geen primitive `int` of `string`)
- Identiteiten worden gegenereerd door de applicatie (UUID), **niet** door de database
  - Auto-increment koppelt entity-constructie aan persistentie en blokkeert domain events vóór opslaan

```php
final class CheckInId
{
    private function __construct(private readonly string $value) {}

    public static function generate(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Constructie

- Private constructor, aangemaakt via named static factory-methoden: `create()` en `reconstitute()`
- `create()` — nieuw domeinobject, genereert eigen ID via `{Id}::generate()` en timestamps
- `reconstitute()` — herbouw vanuit persistentielaag, alle waarden extern aangeleverd

### Mutaties

- Gedragsexpressieve methoden: `complete()`, `changeTitle()`, niet `setCompleted(true)` of `setTitle()`
- Elke mutatie die een state-change veroorzaakt roept `$this->recordThat(new ...)` aan
- Interne timestamps via private `touch()`

### Value Objects

Value Objects **meten, kwantificeren of beschrijven** een concept — het zijn geen dingen.

- Gebruik `readonly class` voor Value Objects (PHP 8.2+): immutabiliteit op taal-niveau
- Validatie in de constructor: een VO is altijd in een geldige staat
- Geen referenties naar Entities in Value Objects (Entities zijn mutabel)
- Gelijkheid via een expliciete `equals()` methode

```php
final readonly class DogName
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Dog name cannot be empty');
        }
    }

    public function value(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### Validatie

- **Attribuut-validatie**: guards in de constructor van het Value Object
- **Whole-object validatie**: aparte `Validator`-klasse die via `validate(ValidationHandler)` wordt aangeroepen vanuit de Entity — de Entity delegeert, valideert zichzelf niet

### Anemic Domain Model (anti-patroon)

Vermijd het Anemic Domain Model: domeinlogica die in Application Services of losse `*Service`-klassen zit terwijl ze thuishoort in Entities en Value Objects. Heuristische check: als Domain Events voornamelijk vanuit Application Services worden afgevuurd in plaats van vanuit Entities, is er waarschijnlijk een Anemic Domain Model.

---

## Domeingebeurtenissen

Domain Events beschrijven iets dat **is gebeurd** in het domein. Ze zijn immutabel en worden benoemd in de verleden tijd.

### DomainEvent interface

```php
interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
```

### Regels

- Naam: `{Naam}{ActieVerleden}` — bv. `CheckInCompleted`, `UserRegistered`, `PostWasCreated`
- Locatie: `Domain/Event/`
- Minimale inhoud: ID van de Aggregate Root + `occurredOn`
- Voeg extra velden toe wanneer downstream Bounded Contexts die nodig hebben voor hun eigen context
- Raised binnen de Aggregate Root via `$this->recordThat(...)`
- Repository persisteert de events **in dezelfde transactie** als de Aggregate
- Dispatch (publish naar listeners) ná persistentie; daarna `clearEvents()` aanroepen

```php
final class CheckInCompleted implements DomainEvent
{
    private readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly string $checkInId,
        public readonly string $dogId,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
```

```php
// In de Aggregate Root:
public function complete(): void
{
    // domeinlogica en invariant-bewaking
    $this->recordThat(new CheckInCompleted(
        checkInId: $this->id->toString(),
        dogId: $this->dogId->toString(),
    ));
}
```

---

## Domeinservices

Gebruik een **Domain Service** voor stateless domeinlogica die niet natuurlijk thuishoort bij één Entity of Value Object.

- Locatie: `Domain/Service/`
- Beschrijvende naam die de Ubiquitaire Taal spreekt: `AvailabilityChecker`, `PriceCalculator`
- Nooit generiek: geen `CheckInService`, geen `DogService`
- Afhankelijk enkel van domain-interfaces, **nooit** van Infrastructure
- Vuistregel: probeer eerst de logica in een Entity of Value Object te plaatsen; kies een Domain Service pas als dat echt niet past

---

## DTO's (View-objecten)

Gebruikt voor data-overdracht van Application-laag naar UI. Geen domeinlogica.

- Naam: `{Naam}View` — bv. `CheckInView`, nooit `CheckInViewModel`
- Locatie: `Application/View/`
- Properties: `public readonly`
- Statische factory `fromDomain()` op de View zelf

```php
final class CheckInView
{
    public function __construct(
        public readonly string $id,
        public readonly string $dogName,
        public readonly string $completedAt,
    ) {}

    public static function fromDomain(CheckIn $checkIn): self
    {
        return new self(
            id: $checkIn->id()->toString(),
            dogName: $checkIn->dogName()->value(),
            completedAt: $checkIn->completedAt()->format('Y-m-d H:i'),
        );
    }
}
```

---

## CQRS

### Commands (schrijven)

- `{Actie}{Naam}Command` — bv. `CompleteCheckInCommand`
- `{Actie}{Naam}Handler` met methode `handle(Command $command): void`
- Command objects zijn immutabel (enkel `public readonly` properties)
- Handler gooit een domeinspecifieke exception bij conflicten, geen generieke `\Exception`
- Na `$repository->save()`: dispatch de domain events en roep `clearEvents()` aan

### Queries (lezen)

- Handler heeft geen apart Query-object tenzij parameters nodig zijn
- Handler retourneert altijd een array van View-objecten, nooit domeinobjecten
- Mapping van domeinobject naar View gebeurt in de handler via `fromDomain()`

```php
final class GetAllCheckInsHandler
{
    public function __construct(
        private CheckInRepository $repository,
    ) {}

    /** @return list<CheckInView> */
    public function handle(): array
    {
        return array_map(
            fn(CheckIn $c) => CheckInView::fromDomain($c),
            $this->repository->findAll()
        );
    }
}
```

---

## Repository

- Interface in `Domain/`, implementatie in `Infrastructure/Persistence/`
- Interface werkt uitsluitend met domeinobjecten (Port)
- Write-side interface is minimaal: `save()` en `byId()` — geen query-methodes op de write-side
- Implementatie gebruikt `reconstitute()` om rijen terug om te zetten naar domeinobjecten
- Serialisatie van Value Objects als JSON in de database
- In-memory implementatie (`InMemory{Naam}Repository`) voor unit tests

```php
interface CheckInRepository
{
    public function save(CheckIn $checkIn): void;
    public function byId(CheckInId $id): ?CheckIn;

    /** @return list<CheckIn> */
    public function findAll(): array;
}
```

---

## Naamgeving

| Wat | Patroon | Voorbeeld |
|---|---|---|
| Entity / Aggregate Root | `{Naam}` | `CheckIn` |
| Identity Value Object | `{Naam}Id` | `CheckInId`, `DogId` |
| Overige Value Objects | beschrijvend | `DogName`, `CheckInOption` |
| Repository interface | `{Naam}Repository` | `CheckInRepository` |
| Repository implementatie | `Pdo{Naam}Repository` | `PdoCheckInRepository` |
| In-memory repository | `InMemory{Naam}Repository` | `InMemoryCheckInRepository` |
| Command | `{Actie}{Naam}Command` | `CompleteCheckInCommand` |
| Command handler | `{Actie}{Naam}Handler` | `CompleteCheckInHandler` |
| Query handler | `Get{Naam(s)}Handler` | `GetAllCheckInsHandler` |
| View / DTO | `{Naam}View` | `CheckInView` |
| Domain Event | `{Naam}{ActieVerleden}` | `CheckInCompleted`, `PostWasCreated` |
| Domain Service | beschrijvend (Ubiquitaire Taal) | `AvailabilityChecker` |

---

## Stijl

- `declare(strict_types=1)` in elk bestand
- `final` op alle klassen tenzij overerving expliciet vereist is (`AggregateRoot`, abstracte basisklassen)
- Geen comments tenzij het *waarom* niet uit de code zelf blijkt
- Named arguments bij meer dan twee parameters of bij constructoraanroepen
- Geen `var_dump` of `die()` in productiecode

# Tactical building blocks

De bouwstenen van het domeinmodel. Kies bewust: identiteit → Entity, meting/beschrijving → Value Object.

---

## Value Objects

Value Objects **meten, kwantificeren of beschrijven** een concept — het zijn geen dingen.

- `final readonly class` (PHP 8.2+): immutabiliteit op taalniveau.
- Validatie in de constructor → een VO is altijd in een geldige staat.
- Geen referenties naar Entities (die zijn mutabel).
- Gelijkheid via expliciete `equals()`, nooit via `===` op de objecten.

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

Een VO kan meerdere velden clusteren (`Money(amount, currency)`, `DateRange(from, to)`)
en gedrag dragen (`Money::add()`, `DateRange::overlaps()`). Gedrag hoort bij de data.

---

## Entities

- **Identiteit bepaalt gelijkheid** — vergelijk via `id()`, niet via property-waarden.
- Interne Entities zijn enkel bereikbaar via hun Aggregate Root.
- Rich Domain Model: gedrag zit ín de Entity, niet in een losse service.

### Encapsulatie

- Properties altijd `private` — nooit `public readonly`. Blootstelling enkel via
  expliciete getters. `readonly` mag alleen samen met `private`.

```php
// correct
private readonly CheckInId $id;
public function id(): CheckInId { return $this->id; }

// niet doen
public readonly CheckInId $id;
```

### Identiteit als Value Object

- De identiteit is altijd een VO (`CheckInId`), nooit een kale `int`/`string`.
- **Applicatie genereert de ID (UUID), niet de database.** Auto-increment koppelt
  constructie aan persistentie en blokkeert Domain Events vóór opslaan.

```php
final class CheckInId
{
    private function __construct(private readonly string $value) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid7()->toString());
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

- Private constructor; aanmaken via named static factory's:
  - `create()` — nieuw domeinobject; genereert eigen ID via `{Id}::generate()` en timestamps.
  - `reconstitute()` — herbouw vanuit de persistentielaag; alle waarden extern aangeleverd.

### Mutaties

- Gedragsexpressieve methoden: `complete()`, `changeTitle()` — niet `setCompleted(true)`.
- Elke state-change roept `$this->recordThat(new ...)` aan.
- Interne timestamps via een private `touch()`.

---

## Aggregates

Een Aggregate is een cluster domeinobjecten met één consistentiegrens en precies één
**Aggregate Root** — de enige klasse waarmee de buitenwereld communiceert.

- Externe objecten refereren enkel aan de Root, nooit aan interne Entities.
- Cross-aggregate referenties via **ID (Value Object)**, niet via objectreferentie.
- Zo klein mogelijk — enkel wat nodig is om invarianten te bewaken.
- Alle invarianten worden afgedwongen door de Root.

```php
// Juist: cross-aggregate referentie via ID
final class CheckIn extends AggregateRoot
{
    private readonly CheckInId $id;
    private readonly HandlerId $handlerId; // ← ID, niet Handler $handler
}

// Fout: directe objectreferentie over aggregate-grenzen
final class CheckIn extends AggregateRoot
{
    private readonly Handler $handler; // ← nooit
}
```

### AggregateRoot-basisklasse

Alle Roots erven van `AggregateRoot`. Deze klasse is **niet** `final`.

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

## Domain Events

Beschrijven iets dat **is gebeurd** — immutabel, benoemd in de verleden tijd.

```php
interface DomainEvent
{
    public function occurredOn(): \DateTimeImmutable;
}
```

Regels:

- Naam: `{Naam}{ActieVerleden}` — `CheckInCompleted`, `UserRegistered`.
- Locatie: `Domain/Event/`.
- Minimale inhoud: ID van de Root + `occurredOn`. Extra velden enkel als een downstream
  Bounded Context ze nodig heeft.
- Geraised in de Root via `$this->recordThat(...)`.
- De repository persisteert events **in dezelfde transactie** als de Aggregate.
- Dispatch (publish) ná persistentie; roep daarna `clearEvents()` aan.

```php
final class CheckInCompleted implements DomainEvent
{
    private readonly \DateTimeImmutable $occurredAt;

    public function __construct(
        public readonly string $checkInId,
        public readonly string $handlerId,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}

// In de Aggregate Root:
public function complete(): void
{
    // invariant-bewaking …
    $this->recordThat(new CheckInCompleted(
        checkInId: $this->id->toString(),
        handlerId: $this->handlerId->toString(),
    ));
}
```

---

## Domain Services

Voor **stateless** domeinlogica die niet natuurlijk bij één Entity of VO hoort.

- Locatie: `Domain/Service/`.
- Beschrijvende naam in de ubiquitaire taal: `AvailabilityChecker`, `PriceCalculator`.
- **Nooit** generiek: geen `CheckInService`, geen `DogService`.
- Afhankelijk enkel van domain-interfaces, nooit van Infrastructure.
- Vuistregel: probeer de logica eerst in een Entity/VO te plaatsen; kies een Domain
  Service pas als dat echt niet past.

---

## Validatie

- **Attribuut-validatie** → guards in de constructor van het Value Object.
- **Whole-object-validatie** → aparte `Validator`-klasse, aangeroepen via
  `validate(ValidationHandler)` vanuit de Entity. De Entity delegeert; ze valideert
  zichzelf niet.

---

## Anti-patroon: Anemic Domain Model

Domeinlogica die in Application Services of losse `*Service`-klassen zit terwijl ze in
Entities/VO's hoort. Heuristiek: als Domain Events voornamelijk vanuit Application
Services worden afgevuurd in plaats van vanuit Entities, heb je waarschijnlijk een
Anemic Domain Model. Verplaats gedrag naar het domeinobject.

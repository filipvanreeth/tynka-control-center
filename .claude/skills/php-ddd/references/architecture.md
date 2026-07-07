# Architectuur: Hexagonal, lagen & CQRS

## Hexagonal Architecture (Ports & Adapters)

Fundament van elke bounded context.

- **Port** = interface in de **Domain**-laag (wat het domein nodig heeft).
- **Adapter** = concrete implementatie in de **Infrastructure**-laag (hoe het technisch werkt).

De Domain-laag kent Infrastructure niet. Alle afhankelijkheden wijzen naar binnen
(richting Domain). Zo is domeinlogica testbaar zonder database of framework — je vervangt
de adapter door een in-memory variant.

## Lagenindeling

```
src/{Context}/
  Domain/
    {AggregateRoot}.php      ← Aggregate roots (erven van AggregateRoot)
    {Entity}.php             ← Interne Entities
    {ValueObject}.php        ← Value Objects
    {Name}Repository.php     ← Repository-interfaces (Ports)
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

**Infrastructure ≠ Presentation.** Infrastructure gaat over technische details
(database, externe services). Presentation is de UI-laag en staat daar los van.

Afhankelijkheidsregel samengevat: `Presentation → Application → Domain ← Infrastructure`.
Domain hangt van niets af; Infrastructure implementeert Domain-ports.

---

## CQRS

Scheid schrijven (Commands) van lezen (Queries). Ze delen het domeinmodel niet
noodzakelijk: Commands gaan door Aggregates; Queries mogen rechtstreeks lezen en
View/DTO's teruggeven.

### Commands (schrijven)

- `{Actie}{Naam}Command` — bv. `CompleteCheckInCommand`. Immutabel (`public readonly`).
- `{Actie}{Naam}Handler` met `handle(Command $command): void`.
- Handler gooit een **domeinspecifieke** exception bij conflicten, geen generieke `\Exception`.
- Na `$repository->save()`: dispatch de Domain Events en roep `clearEvents()` aan.

```php
final class CompleteCheckInHandler
{
    public function __construct(
        private CheckInRepository $repository,
        private EventDispatcher $dispatcher,
    ) {}

    public function handle(CompleteCheckInCommand $command): void
    {
        $checkIn = $this->repository->byId(
            CheckInId::fromString($command->checkInId)
        ) ?? throw new CheckInNotFound($command->checkInId);

        $checkIn->complete();

        $this->repository->save($checkIn);

        foreach ($checkIn->recordedEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }
        $checkIn->clearEvents();
    }
}
```

### Queries (lezen)

- Een apart Query-object alleen als er parameters zijn (`GetCheckInQuery(id)`); anders
  volstaat de handler zelf.
- Handler retourneert altijd **View/DTO's**, nooit domeinobjecten.
- Mapping domein → View gebeurt in de handler via `View::fromDomain()`.

```php
final class GetAllCheckInsHandler
{
    public function __construct(private CheckInRepository $repository) {}

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

## Read-models (DTO's) & view-models

De leeskant geeft **nooit domeinobjecten** aan de UI. Er zijn twee mogelijke soorten
read-side-objecten met een verschillende rol — verwar ze niet.

### Application read-model / DTO — `{Naam}Data` (`Application/Query/`)

De output van een query-handler: pure data, afgestemd op lezen, herbruikbaar over
web/API/CLI. Geen presentatielogica. `public readonly` properties; mapping domein → DTO
via een statische `fromDomain()`.

```php
final readonly class CheckInData
{
    /** @param list<CheckInOptionData> $options */
    public function __construct(
        public string $uuid,
        public HandlerData $handler,
        public array $options,
        public string $createdAt,
    ) {}

    public static function fromDomain(CheckIn $checkIn): self { /* ... */ }
}
```

### Presentation view-model — `{Naam}View` (`Presentation/`, optioneel)

Een object afgestemd op één specifiek scherm/template, dat presentatielogica of compositie
mag bevatten (`isOptionSelected()`, groeperen, formatteren). Dit is een Presentation-patroon
(Presentation Model / ViewModel uit de MVC/MVVM-familie), **geen** DDD-concept — DDD
verplicht of verbiedt het niet.

**Vuistregel:** een `{Naam}View` verdient zijn plek enkel als hij *iets toevoegt* — gedrag
of het samenstellen van meerdere DTO's. Wikkelt hij enkel dezelfde velden opnieuw in zonder
gedrag, dan is het lege indirectie: laat het template dan rechtstreeks het `{Naam}Data`-DTO
consumeren. Bouw view-models **niet speculatief** vooruit.

---

## Repository

- **Interface in `Domain/`** (Port), implementatie in `Infrastructure/Persistence/` (Adapter).
- Interface werkt uitsluitend met domeinobjecten.
- Write-side is minimaal: `save()` en `byId()` — geen query-methodes op de write-side.
  Lees-queries horen bij de query-kant.
- Implementatie gebruikt `reconstitute()` om rijen terug naar domeinobjecten om te zetten.
- Serialiseer Value Objects als JSON in de database.
- Lever een `InMemory{Naam}Repository` voor unit tests.

```php
interface CheckInRepository
{
    public function save(CheckIn $checkIn): void;
    public function byId(CheckInId $id): ?CheckIn;

    /** @return list<CheckIn> */
    public function findAll(): array;
}
```

Implementaties:

- `Pdo{Naam}Repository` — productie, PDO in `Infrastructure/Persistence/`.
- `InMemory{Naam}Repository` — tests; houdt een array bij, geen I/O.

De in-memory variant is niet enkel voor tests handig — hij bewijst dat het domein niet
van de database afhangt (de kern van hexagonal).

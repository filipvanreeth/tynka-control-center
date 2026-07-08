---
name: php-ddd
description: >-
  Moderne Domain-Driven Design in PHP 8.2+ zonder framework — hexagonal
  architecture (Ports & Adapters), CQRS en de tactical patterns uit
  *Domain-Driven Design in PHP*. Gebruik bij het ontwerpen, schrijven of
  reviewen van domeincode (Entity, Value Object, Aggregate, Domain Event,
  Repository, Command/Query, Domain Service) en bij het migreren van
  vanilla-PHP (Controllers/Services/Models) naar een DDD-structuur.
---

# PHP Domain-Driven Design

Fundament voor DDD in moderne PHP zonder MVC-framework — enkel losse
componenten (Symfony-componenten, PHP-DI, FastRoute, PDO). Volgt de patronen
uit *Domain-Driven Design in PHP* (Buenosvinos, Soronellas & Akbary, 2nd ed.).

## Mentor-modus (leerproject)

Dit project is voor de gebruiker óók een **leerproject**: het doel is niet enkel
werkende code, maar *begrijpen wat, waarom en wanneer* je een pattern toepast — de
weg ernaartoe telt evenzeer als het resultaat. Werk daarom als mentor:

- **Leg het waarom uit** — bij elke pattern of keuze: welk probleem lost ze op, en
  wat is het alternatief? Nooit "zo hoort het".
- **Wijs op groei** — benoem actief wat een developer van medior naar senior tilt:
  **abstractie-timing** (wanneer *niet* abstraheren — YAGNI), **testdiscipline**,
  PSR-standaarden, en het afwerken van naden.
- **Her-afleid bij een ander doel** — verandert het criterium (bv. "snel af" →
  "leren"), maak de afweging expliciet en herzie de aanbeveling hardop. De
  senior-vraag is nooit "welke tool is beter?" maar **"beter *waarvoor*?"**.
- **Eerlijk boven vleiend** — geef onderbouwde inschattingen, geen complimenten.

## Wanneer deze skill gebruiken

- Een nieuw domeinconcept modelleren (Entity vs. Value Object vs. Aggregate).
- Twijfel over waar code hoort: Domain, Application, Infrastructure of Presentation.
- Een Command/Query-handler of Repository schrijven.
- Domeincode reviewen op Anemic Domain Model, laklagen of gelekte infrastructuur.
- Vanilla PHP (`Controllers/`, `Services/`, `Models/`) omzetten naar bounded contexts.

## De gouden regels

0. **Strategie eerst** — bepaal de bounded context, de aggregate-grenzen en de
   ubiquitaire taal vóór je tactische code schrijft. Grenzen verkeerd zetten is de
   duurste fout om later te herstellen. (Zie
   [references/strategic-design.md](references/strategic-design.md).)

1. **Ubiquitaire taal** — klasse-, methode- en variabelenamen spreken de taal van
   het domein, niet van techniek. `CheckIn`, niet `CheckInRecord`. `complete()`,
   niet `setCompleted(true)`. Modules heten naar domeinconcepten (`CheckIn/`,
   `Handler/`), **nooit** naar patronen (`Services/`, `ValueObjects/`).

2. **Afhankelijkheden wijzen naar binnen** — Domain kent Application/Infrastructure
   niet. Een Port is een interface in Domain; een Adapter is de implementatie in
   Infrastructure. Zo blijft domeinlogica testbaar zonder database of framework.

3. **Rich Domain Model** — gedrag zit ín Entities en Value Objects, niet in losse
   `*Service`-klassen. Als je domeinlogica in een Application Service schrijft,
   vraag je af of ze niet in het domeinobject hoort. (Zie
   [references/building-blocks.md](references/building-blocks.md) → *Anemic Domain Model*.)

4. **Value Objects zijn onveranderlijk en altijd geldig** — `final readonly class`,
   validatie in de constructor, gelijkheid via `equals()`. Ze meten of beschrijven;
   het zijn geen dingen.

5. **Identiteit is een Value Object, door de applicatie gegenereerd** — `CheckInId`,
   geen kale `int`/`string`. Genereer een UUID in `create()`, niet via database
   auto-increment (dat koppelt constructie aan persistentie en blokkeert Domain
   Events vóór opslaan).

6. **Aggregates zijn klein en bewaken invarianten** — één Aggregate Root per
   consistentiegrens; de buitenwereld praat enkel met de Root. Cross-aggregate
   referenties verlopen via ID-Value-Object, nooit via objectreferentie.

7. **CQRS** — schrijven via `Command` + `Handler` (return `void`, events dispatchen
   na `save()`); lezen via Query-handlers die **View/DTO's** teruggeven, nooit
   domeinobjecten.

8. **Stijl** — `declare(strict_types=1)` overal; `final` tenzij overerving vereist
   is; named arguments bij >2 parameters; geen `var_dump`/`die()` in productiecode.

## Structuur van een bounded context

```
src/{Context}/
  Domain/          Aggregate Roots, Entities, Value Objects, Repository-interfaces (Ports), Event/, Service/
  Application/     Command/ (schrijven) · Query/ (lezen) · View/ (DTO's)
  Infrastructure/  Persistence/ — concrete repositories (Adapters, PDO/InMemory)
  Presentation/    Http/ · Api/ · Console/
```

`Infrastructure` (technische details: DB, externe services) staat los van
`Presentation` (de UI-laag). Verwar ze niet.

## Referenties (laad wat je nodig hebt)

- [references/strategic-design.md](references/strategic-design.md) — ubiquitaire taal,
  subdomeinen, bounded contexts, context mapping, aggregate-grenzen en read-models/CQRS.
  Ontwerp dit eerst; het bepaalt waar tactische code hoort.
- [references/building-blocks.md](references/building-blocks.md) — Entity, Value Object,
  Aggregate + AggregateRoot-basisklasse, Domain Event, Domain Service, validatie,
  het Anemic-Domain-Model-antipatroon. Mét codevoorbeelden.
- [references/architecture.md](references/architecture.md) — Hexagonal (Ports &
  Adapters), lagenindeling, CQRS (Commands & Queries), Repository-contract,
  in-memory repositories voor tests.
- [references/naming.md](references/naming.md) — volledige naamgevingstabel en
  richtlijnen voor ubiquitaire taal.
- [references/migration.md](references/migration.md) — playbook om vanilla PHP
  gefaseerd (strangler-fig) naar DDD te migreren, met een concrete checklist.

## Snelle beslisboom

- Heeft het concept identiteit die door de tijd voortleeft? → **Entity** (mogelijk
  Aggregate Root). Zo niet, beschrijft/meet het iets? → **Value Object**.
- Logica die niet natuurlijk bij één Entity of VO hoort en stateless is? →
  **Domain Service** (beschrijvende naam, bv. `AvailabilityChecker`; nooit `XService`).
- Data van Application naar UI? → **View/DTO** (`public readonly`, `fromDomain()`).
- Iets is gebeurd dat downstream relevant is? → **Domain Event** (verleden tijd),
  `recordThat(...)` binnen de Aggregate Root.

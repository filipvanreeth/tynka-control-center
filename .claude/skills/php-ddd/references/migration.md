# Migratie: vanilla PHP → DDD

Playbook om een bestaande frameworkloze PHP-app (mappen als `Controllers/`,
`Services/`, `Repositories/`, `Entities/`, `Enums/`) gefaseerd naar bounded contexts te
brengen — zonder big-bang-herschrijving. Aanpak: **strangler fig** — nieuwe structuur
groeit rond de oude, per feature, tot de oude code weg kan.

## Uitgangspunt herkennen

Tijdens de migratie staan oud en nieuw naast elkaar; dat is normaal en tijdelijk.
Symptomen die je in de tussentoestand ziet (en wilt wegwerken):

- Technische mappen (`Controllers/`, `Services/`, `Models/`) náást contextmappen
  (`CheckIn/`, `Handler/`).
- Entities met `public readonly` en een nullable `?int $id` + `setId()` (database
  auto-increment) i.p.v. een `{Naam}Id`-VO.
- `\PDO` rechtstreeks geïnjecteerd in query-handlers i.p.v. achter een repository.
- Aggregates zonder `AggregateRoot`-basisklasse en zonder Domain Events.
- Losse `*Service`-klassen die domeinlogica dragen (Anemic Domain Model).
- `var_dump()`, `die()`, placeholder-excepties (`throw new Exception('dddd')`) en
  uitgecommentarieerde oude code in handlers.
- Handmatige bedrading met `new` in de front controller i.p.v. een DI-container.

## Fasen

### 1. Kies één bounded context en teken zijn taal

Begin bij één samenhangend concept (bv. `CheckIn`). Benoem de aggregaten, entities en
value objects in de ubiquitaire taal vóór je code verplaatst. Zie
[naming.md](naming.md).

### 2. Bouw het domeinhart eerst, zonder infrastructuur

Maak `Domain/` met de Aggregate Root, VO's (incl. `{Naam}Id`), en de
Repository-**interface** (Port). Geef gedrag aan de Entity (`complete()`,
`changeHandler()`), niet aan een service. Genereer ID's in `create()` via UUID.

### 3. Zet een in-memory adapter neer

Implementeer `InMemory{Naam}Repository` in `Infrastructure/`. Nu kun je het domein
volledig unit-testen zonder database — dat bewijst dat de afhankelijkheden naar binnen
wijzen.

### 4. Verplaats lezen naar Query-handlers

Vervang lees-`*Service`-methoden door Query-handlers die **View/DTO's** teruggeven.
Haal `\PDO` uit de handler-signatuur: lees via de repository, of introduceer een
expliciete read-model/`Pdo…`-adapter achter een interface. Verwijder `var_dump`/`die`.

### 5. Verplaats schrijven naar Commands

Zet schrijf-`*Service`-methoden (bv. `recordCheckIn()`) om naar een
`{Actie}{Naam}Command` + `Handler`. Laat de handler het Aggregate laden, een
gedragsmethode aanroepen, opslaan, en daarna events dispatchen + `clearEvents()`.

### 6. Introduceer Domain Events

Laat state-changes `recordThat(...)` aanroepen binnen de Root. Persisteer events in
dezelfde transactie; dispatch ná opslaan. Nu verhuist gedrag definitief uit de services.

### 7. Vervang de in-memory adapter door PDO

Schrijf `Pdo{Naam}Repository` met `reconstitute()` en JSON-serialisatie van VO's.
De domein- en applicatielaag veranderen niet — enkel de adapter wisselt. De in-memory
variant blijft bestaan voor tests.

### 8. Verhuis de controller naar Presentation

Verplaats de web-controller naar `{Context}/Presentation/Http/`. De controller roept
enkel Command/Query-handlers aan en mapt request→command en view→response. Geen
domeinlogica in de controller.

### 9. Ruim de oude structuur op

Als alle features van een context over zijn: verwijder de bijhorende `Controllers/`,
`Services/`, `Repositories/`, `Entities/`, `Enums/`. Centraliseer bedrading in een
DI-container (bv. PHP-DI) i.p.v. handmatige `new` in de front controller.

## Definition of done per context

- [ ] Geen technische mapnamen meer voor deze context (`Services/`, `Models/` weg).
- [ ] Aggregate Root erft van `AggregateRoot`; identiteit is een `{Naam}Id`-VO.
- [ ] Alle Entity-properties `private`; geen `public readonly`, geen `setId()`.
- [ ] ID's app-gegenereerd (UUID), niet via database auto-increment.
- [ ] Repository-interface in `Domain/`, PDO- én InMemory-adapter in `Infrastructure/`.
- [ ] Lezen via Query-handlers die View/DTO's teruggeven; geen `\PDO` in de handler.
- [ ] Schrijven via Command + Handler; events gedispatcht ná `save()`.
- [ ] Gedrag zit in Entities/VO's, niet in `*Service`-klassen (geen Anemic Domain Model).
- [ ] Geen `var_dump`/`die()`/placeholder-excepties/dode code meer.
- [ ] Unit tests draaien op de InMemory-repository zonder database.

## Volgorde over contexten heen

Migreer context per context, niet laag per laag. Eén volledig afgewerkte `CheckIn` is
meer waard dan alle contexten half af. Begin bij de context met de rijkste
domeinlogica (daar levert DDD het meest op) of de meest gewijzigde (daar betaalt de
opschoning zich het snelst terug).

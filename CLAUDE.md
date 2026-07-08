# Tynka Control Center — Claude Code richtlijnen

## Project

PHP-applicatie gebouwd op DDD-principes met CQRS, **zonder MVC-framework**. Losse
componenten: PHP-DI, FastRoute, phpdotenv, ramsey/uuid, PDO (SQLite) en plain-PHP
templates (`PhpTemplateEngine`).

- Namespace root: `TynkaControlCenter\` → `src/`
- PHP 8.2+ · PHPStan level 9 · PHP_CodeSniffer · PHPUnit 12
- `composer php:analyse` (phpcs + phpstan) · `composer php:fix` (phpcbf)

## DDD-conventies → `/php-ddd`

De volledige DDD-canon (Entities, Value Objects, Aggregates, Domain Events, CQRS,
Repositories, naamgeving, hexagonal architecture) staat in de projectskill **`php-ddd`**
(`.claude/skills/php-ddd/`). Raadpleeg die bij het schrijven of reviewen van domeincode;
roep hem expliciet op met `/php-ddd`. Herhaal die regels hier niet — één bron van waarheid.

## Referentie

> **DDD-boek:** *Domain-Driven Design in PHP* (2nd ed.) — Buenosvinos, Soronellas & Akbary
> Lokaal: `/Users/filip/Library/Mobile Documents/com~apple~CloudDocs/Books/ddd-in-php.pdf`
> Implementaties volgen de patronen uit dit boek.

## Modulekaart

Bounded contexts (doelstructuur):

- `src/CheckIn/` — kerncontext: check-ins, opties en categorieën.
- `src/Handler/` — de begeleiders (hondenuitlaters).
- `src/Common/` — gedeelde Value Objects & interfaces (`Slug`, `TranslatedText`,
  `Locale`, `Avatar`, `Translator`).

Gedeelde technische laag: `src/Config/` (AppConfig), `src/Infrastructure/Templating/`
(port `TemplateEngine` + adapter `PhpTemplateEngine`; controllers hangen af van de port,
Twig-klaar). Front controller + bedrading: `public/index.php`.

## Migratiestatus (in uitvoering)

De app migreert van een vanilla-PHP-structuur naar DDD (strangler fig). Oud en nieuw
staan tijdelijk náást elkaar. **Nog op te schonen / weg te werken:**

- Alle top-level legacy-mappen genoemd naar technische patronen zijn verwijderd:
  `src/Controllers/`, `src/Services/`, `src/Repositories/`, `src/Application/`,
  `src/Presentation/`. `src/` bevat nu enkel bounded contexts (`CheckIn/`, `Handler/`),
  de shared kernel (`Common/`), `Config/` en de gedeelde technische laag (`Infrastructure/`).
- ~~`\PDO` in de query-handlers~~ **opgelost (Fase C):** de leeskant loopt nu via de port
  `CheckInReadModel` (`CheckIn/Application/Query/`) + adapter `PdoCheckInReadModel`
  (`CheckIn/Infrastructure/Persistence/`). `\PDO` wordt nu als **container-factory** gebouwd
  (`config/container.php`) en zit verder enkel in de twee Persistence-adapters — niet meer
  los in `index.php`.
- ~~Bedrading handmatig met `new` in `index.php`; PHP-DI niet in gebruik~~ **opgelost:**
  zie *DI-container afgerond* hieronder.
- Domain Events & Command-handlers nog niet in gebruik (de `AggregateRoot`-basisklasse
  staat er wel; events recorden = latere stap).
- **God-controller:** `CheckInController` heeft 9 collaborators (bleek pijnlijk bij het
  optuigen van de eerste unit-test) → splitsen in **single-action controllers**.
- **Rij→DTO-mapping nog niet gecentraliseerd** (recent een `id` vs `uuid`-bug in
  `GetAllCheckInsHandler` gefixt) → centraliseren via `CheckInData::fromDomain()`.
- **PHPStan level 9 nog niet groen:** resterende `missingType.iterableValue`-gaten in
  DTO's/views → generics op collecties (`array<int, CheckInData>`).
- **phpcs:** vastgelegd op **PSR-12** (`phpcs.xml.dist`, voorheen viel het terug op PEAR);
  26 pre-existing auto-fixes staan nog open (`composer php:fix`, eigen chore-commit).
- `edit()`-route blijft geparkeerd/kapot: de actie is wél al omgezet naar
  `Request → Response`, maar de body draagt nog 6 pre-existing phpstan-errors (verouderde
  `GetCheckInByIdQuery`-signatuur, `$checkIn->uuid`, `render(path:)`,
  `getAllCheckInActivitiesHandler->handle()` zonder `$query`). Fixen zodra de rij→DTO-mapping
  gecentraliseerd is; **verhuis daarbij `getHtmlLang()`** van de `Translator`-poort/-adapter
  naar de **presentatielaag** (presentatie-formattering hoort niet op een domein-poort).

**Fase A afgerond (index-pagina):** debug-cruft (`var_dump`/`die()`/dode code) verwijderd,
`index()` herleid tot één render, en de leeskant leest via een **optie-lijst read-model**
(`CheckInData->options`, `TopHandlerData`) — templates lezen enkel nog DTO-properties, geen
domeinobject-methodes. De indexpagina rendert foutloos.

**Fase B afgerond (CheckIn-aggregaat):** `CheckIn` erft van `AggregateRoot`, heeft private
properties met getters, en identiteit via het `CheckInId`-VO (app-gegenereerde uuid7 uit de
`uuid`-kolom; de DB-`id` is enkel een surrogaatsleutel). Private constructor met
`create()`/`reconstitute()`; `setId()` is weg. De handler-referentie is nu een `HandlerId`-VO
(geen kale string, geen `Handler`-object) en de vier booleans zijn vervangen door een
`list<CheckInOptionId>` (`selectedOptions`). Het schrijfmodel is dus ontkoppeld van de
opslagvorm: de repository vertaalt de optie-set ↔ de vier DB-kolommen.

**CQRS-write + Repository-Port afgerond:** het generieke `CheckInService` is verwijderd.
Schrijven verloopt nu via `RecordCheckInCommand` + `RecordCheckInHandler`
(`CheckIn/Application/Command/`). De write-side heeft een **Port** `CheckInRepository`
(interface in `CheckIn/Domain/`, `save()`/`byId()`), geïmplementeerd door de **Adapter**
`PdoCheckInRepository` (`CheckIn/Infrastructure/Persistence/`). Command-handler en controller
hangen af van de interface, niet van een concrete klasse. De schrijfketen (POST →
`RecordCheckInHandler` → `save()`) is geverifieerd en schrijft de juiste kolommen weg.

**DI-container afgerond:** de handmatige `new`-bedrading is uit `index.php` gehaald. PHP-DI
bouwt nu alles via `config/container.php` (poort→adapter-bindings + scalar-factories voor
`AppConfig`/`PDO`/tabelnamen/vertaalpaden; alle handlers en de controller **autowired**);
`config/routes.php` bevat de routetabel. De front controller is dun: bootstrap → container →
dispatch. Nieuwe top-level map `config/`.

**Fase HTTP afgerond (Request → Response):** de presentatielaag draait op **PSR-7**
(`nyholm/psr7` + `nyholm/psr7-server`). Controller-acties zijn `Request → Response` — geen
superglobals, geen `echo`/`exit`, redirects zijn data (`302 + Location`). Nieuwe gedeelde
HTTP-laag in `src/Infrastructure/Http/`: de **poort** `Session` + **adapter** `PhpSession`
(sessie/flash; PSR-7 dekt sessies niet) en een `ResponseEmitter` (enige plek met
`header()`/`echo`). De front controller leest de globals één keer in
(`ServerRequestCreator::fromGlobals()`), de access-guard geeft een `Response(403)`, route-params
reizen als request-attributen. Eerste **unit-test** op de controller + herbruikbare in-memory
dubbels (`tests/Support/InMemorySession`, `InMemoryCheckInRepository`); `phpunit.xml.dist`
toegevoegd. Conventies vastgelegd in `php-ddd` → gouden regel 9 + `references/presentation-http.md`.
Nog open: guard als PSR-15-middleware (Fase 2).

Het volledige stappenplan staat in `php-ddd` → `references/migration.md`.

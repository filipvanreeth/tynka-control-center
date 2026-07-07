# Strategisch ontwerp

De tactische bouwstenen ([building-blocks.md](building-blocks.md)) vertellen je *hoe* je
een Entity of Aggregate schrijft. Strategisch ontwerp vertelt je *wat* je moet modelleren
en *waar de grenzen liggen* — het duurdere om later te herstellen. Ontwerp dit **eerst**,
zodat tactische code niet in de verkeerde context belandt.

> Aanvullende leesstof op *DDD in PHP* (dat vooral tactisch is): Vernon —
> *Domain-Driven Design Distilled* (beknopt, modern) en Khononov — *Learning DDD*
> (sterk op strategisch ontwerp & context mapping).

---

## Ubiquitaire taal (de kern)

Eén taal, gedeeld tussen code en domeinexperts, binnen één bounded context. Niet enkel
naamgeving — de taal stuurt het *model*.

- Elke term in de code komt uit gesprekken met de domeinexpert. Als jij een woord
  gebruikt dat de expert niet herkent (of omgekeerd), klopt het model nog niet.
- Dezelfde term kan in twee contexten iets ánders betekenen. Dat is geen probleem op te
  lossen met één gedeelde klasse — het is een **contextgrens** (zie hieronder).
- Wijzigt de taal, dan wijzigt de code. Hernoem meteen; laat geen verouderde termen staan.

Heuristiek: kun je een methode-/klassenaam hardop voorlezen aan een niet-programmeur en
klopt de zin? (`checkIn.complete()`, `handler.walk(dog)`) Zo niet, herbenoem.

---

## Subdomeinen: waar investeer je?

Niet elk deel van het systeem verdient evenveel modelleerinspanning.

- **Core domain** — waar je onderscheidend bent; hier gaat je beste DDD-werk naartoe.
- **Supporting subdomain** — nodig maar niet onderscheidend; eenvoudiger modelleren mag.
- **Generic subdomain** — een opgelost probleem (auth, mail); koop/gebruik een bestaande
  oplossing, bouw het niet zelf met rijk domeinmodel.

Vuistregel: besteed geen aggregate-design aan een generiek subdomein, en versimpel je
core domain niet tot een CRUD-tabel.

---

## Bounded Contexts

Een **Bounded Context** is een expliciete grens waarbinnen één model en één ubiquitaire
taal geldig zijn. Buiten de grens gelden andere regels.

- Eén context = één module (`src/{Context}/`). De grens is fysiek zichtbaar in de map- en
  namespacestructuur.
- Deel geen Entities/Value Objects over contextgrenzen. Wat je deelt, deel je bewust via
  een contract (ID, DTO, event) — niet via een gedeelde klasse.
- Twee betekenissen van hetzelfde woord → twee contexten, elk met hun eigen model.
  `Handler` in een `CheckIn`-context (wie de check-in deed) kan iets anders zijn dan
  `Handler` in een toekomstige `Scheduling`-context.

---

## Context Mapping

Hoe contexten zich tot elkaar verhouden. Kies de relatie bewust:

- **Shared Kernel** — een klein gedeeld model tussen twee teams. Krachtig maar duur:
  wijzigingen vereisen afstemming. Houd het minimaal (bv. `Common/` met puur generieke
  Value Objects zoals `Slug`, `Locale`).
- **Customer/Supplier** — de ene context levert aan de andere; downstream stemt af op
  upstream.
- **Conformist** — downstream neemt het model van upstream over zonder vertaling.
- **Anti-Corruption Layer (ACL)** — downstream vertaalt het externe model naar het eigen
  model. Gebruik dit tegen legacy of externe systemen zodat hun begrippen jouw domein
  niet vervuilen.

In deze codebase: `Common/` is een bewuste Shared Kernel van generieke Value Objects.
Alles wat *domeinspecifiek* is (CheckIn-opties, Handlers) hoort in zijn eigen context,
niet in `Common/`.

---

## Aggregate-grenzen (de duurste beslissing)

De grens van een Aggregate = de grens van een **transactie** en van **consistentie**.

Regels om vooraf goed te zetten (herstellen is duur):

1. **Ontwerp klein.** Eén Aggregate Root + het minimum aan objecten om de invarianten te
   bewaken. Grote aggregaten geven concurrency- en performanceproblemen.
2. **Sterke consistentie binnen, eventuele consistentie buiten.** Alles binnen de grens is
   na elke transactie consistent. Wat elders consistent mag worden, hoort in een ánder
   aggregaat — gekoppeld via een Domain Event.
3. **Refereer naar andere aggregaten via ID**, nooit via objectreferentie. Zo blijven
   grenzen scherp en aggregaten klein.
4. **Eén transactie = één aggregaat.** Wijzig je in één use-case meerdere aggregaten
   tegelijk transactioneel, dan liggen je grenzen waarschijnlijk verkeerd.

Vraag bij twijfel: *"Moet regel X waar zijn op het exacte moment dat ik opsla, of mag hij
even later waar worden?"* Meteen → binnen de grens. Even later → ander aggregaat + event.

---

## Read models & CQRS (waarom lezen anders mag)

Strategisch belangrijk voor de UI: de **leeskant hoeft het domeinmodel niet te gebruiken.**

- Commands gaan door Aggregates (bewaken invarianten). Queries mogen een eigen
  **read-model** hebben dat rechtstreeks op de view is afgestemd — een DTO met precies de
  velden die het scherm nodig heeft, niet meer.
- Dwing dus geen Entity-methodes af in je templates. Een view leest een DTO/View-object;
  de mapping domein → DTO gebeurt in de query-handler.
- Het read-model mag denormaliseren en velden combineren voor gemak. Dat is geen
  "vuil" model — het is een bewust ander model voor een ander doel.

Consequentie voor de praktijk: als een template een `->type()->name()` op een
domeinobject aanroept, is dat een teken dat de leeskant nog niet als read-model is
ontworpen. Verrijk het View/DTO tot het alles draagt wat de view nodig heeft, en laat het
template enkel properties lezen.

# Migratieplan — presentatie splitsen in Web + API

> Strangler-fig, gefaseerd. Scope: **web en API** als twee driving adapters over
> dezelfde application-kern. **Console = later** (het plan houdt er wél rekening mee).
>
> Kernprincipe: **domein + application veranderen niet, geen byte.** `RecordCheckInCommand`,
> `RecordCheckInHandler`, de query-handlers en de `CheckInData`-DTO's zijn al kanaal-neutraal.
> Alleen de presentatie-/delivery-laag vermenigvuldigt zich. Web en API zijn **geen** aparte
> bounded contexts — drie adapters, één kern.

## Uitgangspunt (nu)

- Eén front controller `public/index.php`: bootstrap (dotenv, `session_start`, container),
  PSR-7-request uit globals, **hardcoded access-guard** (`access_token`), FastRoute-dispatch,
  handmatige `match()` op `CheckInController`, emit.
- `config/routes.php`: logische namen (`check-in.submit`, …) die de `match()` naar methodes mapt.
- `CheckInController` bundelt alle web-acties (`index`, `edit`, `handleCheckInSubmission`).
- `RecordCheckInForm::fromRequest(array $body, array $activityIds)` is **al kanaal-neutraal**
  (neemt een array, geeft command-of-foutcodes terug) → herbruikbaar door de API.

## Afhankelijkheidsketen (wat ontgrendelt wat)

```
Fase 0  cleanup ─────────────┐  (submit is nu kapot; eerst repareren)
Fase 1  gedeelde bootstrap ──┤  (nodig voor meerdere thin entrypoints + console later)
Fase 2  routing per kanaal ──┤  (route draagt controller-ref → weg met match())
Fase 3  guard → middleware ──┴─→ web & api divergeren hier (sessie- vs token-guard, HTML- vs JSON-fout)
Fase 4  presentatie/​Web + /​Api opdelen  →  eerste API-read (DTO → JSON, géén nieuwe app-code)
Fase 5  API write-slice (JSON → zelfde command → zelfde handler)
Fase 6  consolideren + tests + CLAUDE.md bijwerken
```

Elke fase is los shipbaar en eindigt met een **test** (jouw discipline-groeipunt).

---

## Fase 0 — Vooropruimen (geen gedragswijziging)

De submit-actie is momenteel kapot: `var_dump()`/`die()` staan nog in
`CheckInController::handleCheckInSubmission()` én `RecordCheckInForm::fromRequest()`. Splitsen
begin je niet op kapotte code.

- Verwijder alle `var_dump`/`die()` uit `handleCheckInSubmission()` en `RecordCheckInForm`.
- Verifieer dat POST `/checkin` weer een 302 + flash geeft.

**Klaar:** submit werkt end-to-end; bestaande controller-test groen.

---

## Fase 1 — Gedeelde bootstrap extraheren

- Nieuw: `config/bootstrap.php` → bouwt en retourneert de PHP-DI-container (dotenv-load +
  `ContainerBuilder`). **Geen** `session_start()` hierin — dat is web-specifiek (API/console
  zijn stateless).
- `public/index.php` wordt: `$container = require BASE_PATH.'/config/bootstrap.php';` +
  `session_start()` blijft in het web-entrypoint.

**Ontgrendelt:** meerdere dunne entrypoints die dezelfde container delen (straks `bin/console`).
**Klaar:** web draait identiek; geen container-bedrading meer in de front controller.

---

## Fase 2 — Routing per kanaal + controller-referentie in de route

- Splits `config/routes.php` → `routes/web.php` en `routes/api.php`. API onder prefix
  **`/api/v1/`** (versie nú vastleggen kost niets en spaart later pijn — een API-oppervlak is
  een contract).
- Verander de route-payload van een logische string naar een concrete referentie
  `[ControllerClass::class, 'methode']` (of een invokable single-action controller).
- Front controller resolvet de controller via de container i.p.v. de handmatige `match()`.
  De groeiende `match` verdwijnt; een kanaal toevoegen = een routebestand, geen `match`-tak.
- Web-routes wijzen naar de bestaande `CheckInController`-methodes → geen gedragswijziging.
  `routes/api.php` bestaat maar is nog (bijna) leeg.

**Ontgrendelt:** API-routes toevoegen zonder de web-dispatch aan te raken.
**Klaar:** web draait via de nieuwe route-resolutie; `match()` weg.

---

## Fase 3 — Access-guard als PSR-15 middleware (de forcing function)

De guard zit nu hardcoded in `index.php` en kan maar één ding. Web wil een **sessie-token**
+ HTML-403; API wil een **bearer/header-token** + **JSON-401**. Dat dwingt de guard eruit.

- Minimale middleware-pijplijn (request → stack → handler → response). Hand-rollen (~30–40
  regels) past bij de "losse componenten"-ethos; `nyholm/psr7` levert geen dispatcher. Optie:
  een klein pakket (`relay/relay`) als je liever standaard blijft — afweging, geen must.
- `Web\SessionAccessGuard`: leest sessie/`access_token`, faalt met `Response(403, HTML)`.
- `Api\TokenAccessGuard`: leest `Authorization`-header, faalt met `Response(401, JSON)`.
- Pijplijn per kanaal gekozen: front controller branch't op de `/api`-prefix (of, later,
  twee entrypoints `index.php` + `api.php` — beide valide; prefix-branch heeft nu de minste
  webserver-config).

> Shortcut mogelijk: voor de allereerste API-slice mag je de guard eventjes inline in het
> api-tak zetten en de middleware-abstractie in deze fase pas netjes maken. Houd het klein.

**Ontgrendelt:** echt verschillende auth + foutformaten per kanaal.
**Klaar:** web-guard als middleware; api-guard-stub geeft JSON-401; guard weg uit `index.php`.

---

## Fase 4 — Presentatie opdelen + eerste API-read (Responder-splitsing)

- Herstructureer `CheckIn/Presentation/` → `Web/` en `Api/`. Bestaande controllers → `Web/`.
- De **Responder** is de outputkant: web rendert HTML (`PhpTemplateEngine`), API serialiseert.
  Introduceer een kleine `Api\JsonResponder` (zet `Content-Type: application/json`, `json_encode`).
- Eerste API-endpoint (read): `GET /api/v1/checkins` → roept **dezelfde**
  `GetAllCheckInsHandler` aan, serialiseert `CheckInData[]` naar JSON. **Nul nieuwe app-code** —
  dit is het bewijs dat je DTO-discipline zich uitbetaalt.
- Definieer één JSON-envelope-conventie meteen (bv. `{ "data": [...] }` en voor fouten
  `{ "errors": { "veld": "code" } }`) zodat alle endpoints consistent zijn.

> Let op: `json_encode` op een DTO lekt de DTO-vorm naar de draad. Voor nu oké; zodra veld-
> naamgeving/casing van het API-contract afwijkt van de DTO, schuif een dunne transformer in
> de `JsonResponder` i.p.v. de DTO te verbouwen.

**Klaar:** `GET /api/v1/checkins` geeft geldige JSON; web-index onveranderd; unit-test op de
API-read met de bestaande in-memory dubbels (géén sessie nodig — stateless).

---

## Fase 5 — API write-slice: record check-in via JSON

- `Api\RecordCheckInController`: leest JSON-body → `RecordCheckInForm::fromRequest(json_decode
  ($body, true), $activityIds)` → **dezelfde** `RecordCheckInHandler`.
- Fouten: `422` + JSON-foutcodes-array (géén flash, géén redirect). Succes: `201` (+ resource
  of `Location`) of `204`.
- Dit sluit de hexagon: identiek command, identieke handler, andere in/out-adapter. De
  gedeelde `RecordCheckInForm` verhuist naar een kanaal-neutrale plek als web én api hem delen.

**Klaar:** `POST /api/v1/checkins` schrijft dezelfde kolommen weg als de web-POST; unit-test
dekt zowel de happy path (201) als validatiefouten (422 + codes).

---

## Fase 6 — Consolideren

- DI: registreer de api-controllers (grotendeels autowired = gratis).
- Tests: elke API-controller met in-memory dubbels; JSON-asserties. Web-tests blijven groen.
- Werk de **migratiestatus in `CLAUDE.md`** bij (nieuwe fase afgerond, console geparkeerd).
- Noteer expliciet wat naar de console-fase doorschuift (gedeelde bootstrap staat er al klaar
  voor; `bin/console` + `symfony/console` wordt dan het derde dunne entrypoint).

---

## Beslissingen om vroeg vast te leggen

| Onderwerp | Aanbeveling | Waarom nu |
|---|---|---|
| API-versionering | `/api/v1/` vanaf dag één | Contract; achteraf toevoegen is pijnlijk |
| JSON-fout-envelope | één vorm (`{ "errors": {…} }`) | Consistentie over endpoints |
| Middleware-runner | hand-roll klein (of `relay/relay`) | Ethos "losse componenten"; guard divergeert |
| Entrypoint-vorm | nu prefix-branch, later evt. `api.php` | Minste churn nu, open naar console |
| Serialisatie | `json_encode(DTO)` nu, transformer zodra contract afwijkt | Voorkomt vroege over-engineering |

## Wat expliciet NIET gebeurt

- Geen aparte "Api" bounded context; geen gedupliceerde domein-/validatielogica.
- Geen wijziging aan domein of application-handlers.
- Console nog niet — maar Fase 1 (bootstrap) en Fase 2 (route-bestanden) leggen het pad.

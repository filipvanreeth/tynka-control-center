# Login-slice — Access-context + auth-middleware

> Realiseert **Fase 3** ("guard → middleware") uit
> [migration-web-api-split.md](migration-web-api-split.md), maar uitgebreid tot echte
> authenticatie. Scope: **inloggen als handler + auth-pijplijn**. De admin-sectie en het
> activity/category-schrijfmodel komen daarná (aparte slice).
>
> **Modelbeslissing (vast):** een `Account` (Access-context) is *niet* een `Handler`. Het
> linkt via `HandlerId` naar de bestaande Handler. Rol zit op het account. Auth is een
> **generiek subdomein** → thin houden, `password_hash`/`password_verify`, géén rijk
> aggregate-design. Zie strategic-design.md (regel 36-37, 51-55).

## Nieuwe bounded context: `src/Access/`

```
Access/Domain/
  Account.php              (AccountId, Email, PasswordHash, Role, HandlerId) — klein
  AccountId.php            (uuid-VO, zoals CheckInId)
  Email.php                (VO, valideert vorm)
  PasswordHash.php         (VO; wrapt password_hash/password_verify — verify() erin)
  Role.php                 (PHP enum: Handler | Admin)
  AccountRepository.php    (poort: byEmail, byId, save)
Access/Application/
  AuthenticateUser.php     (email+wachtwoord → Account of null; verandert géén state)
Access/Infrastructure/Persistence/
  PdoAccountRepository.php (+ `accounts`-tabel)
Access/Presentation/Web/
  LoginController.php       (GET form, POST authenticate → sessie → redirect)
  LogoutController.php
  LoginForm.php             (kanaal-neutrale input, zoals RecordCheckInForm)
```

## Stappen (elk los shipbaar, elk eindigt met een test)

### Stap A — Access-domein + persistence (géén HTTP)
- `Account`, `AccountId`, `Email`, `PasswordHash`, `Role`, `AccountRepository`-poort.
- `PdoAccountRepository` + `accounts`-tabel (volg het schema-patroon van `checkins`).
- In-memory dubbel `InMemoryAccountRepository` in `tests/Support` (zoals de andere).
- **Klaar:** unit-tests op `Account` (rol, wachtwoord-verify) en op de repository-roundtrip.

### Stap B — Authenticatie-use-case
- `AuthenticateUser`: `byEmail` → `PasswordHash::verify()` → `?Account`. Read + verify,
  géén state-wijziging (de sessie zetten gebeurt aan de web-rand).
- **Klaar:** test happy path + fout wachtwoord + onbekende email, met de in-memory repo.

### Stap C — Seed van het eerste account ✅
- **Phinx ingevoerd** voor schema-migraties (vervangt `database/migrate.php`): `phinx.php` +
  `db/migrations/*` (checkins mét `food`/`snack`, accounts, alles `NOT NULL`). Dev-DB reset.
- Seeding blijft **app-side** door het domein: `bin/seed-admin.php` maakt via de
  `AccountRepository` één admin-account (idempotent, env-gedreven `SEED_ADMIN_*`) — géén raw
  Phinx-insert, zodat `PasswordHash`/`Account::register` de invarianten bewaken. Wegwerp tot de
  admin-sectie/console accounts kan aanmaken.
- **Klaar:** account in de DB; `AuthenticateUser` logt er end-to-end mee in (geverifieerd).

### Stap D — Middleware-pijplijn (de kern van Fase 3)
- Minimale PSR-15-achtige runner (hand-roll ~40 regels; ethos "losse componenten").
- `AuthenticationMiddleware`: leest `account_id` uit de sessie, laadt het account, hangt
  het als request-attribuut `currentAccount` (of `null`).
- `AuthorizationMiddleware`: leest de rol-eis van de route; web → redirect naar `/login`,
  API → 401/403 JSON. (Admin-rol-eis wordt straks door de admin-sectie gebruikt.)
- Front controller (web): `authn → authz → dispatch`. **De `access_token`-hack verdwijnt.**
- **Klaar:** unit-tests op beide middlewares (ingelogd / niet / verkeerde rol).

### Stap E — Login-UI + integratie
- `LoginController` (GET `/login`, POST `/login`), `LogoutController` (POST `/logout`).
- Routes in `routes/web.php`; login-view-template.
- Bij succes: `$session->set('account_id', …)` → redirect `/`. Sessie-`access_granted`
  vervalt; aanwezigheid van `account_id` is voortaan "ingelogd".
- **Cadeautje:** check-in-`handler` niet langer vrij intypen maar = `currentAccount`'s
  `HandlerId`; het tekstveld uit `RecordCheckInForm` verdwijnt.
- **Klaar:** controller-test (login-succes/-mislukking) + CLAUDE.md/migratiestatus bijwerken.

## Beslissingen om vroeg vast te leggen

| Onderwerp | Aanbeveling | Waarom |
|---|---|---|
| Login = command of query? | Query-achtig (`AuthenticateUser`), sessie zetten aan de rand | Domein wijzigt niet; alleen de web-sessie |
| Rol-representatie | PHP `enum Role` | Gesloten set, type-safe, idiomatisch 8.2 |
| Middleware-runner | hand-roll klein (of `relay/relay`) | Zelfde afweging als Fase 3 |
| Wachtwoord | `password_hash` (bcrypt/argon2) in `PasswordHash`-VO | Auth = generiek; nooit zelf crypto |
| Handler-link | `HandlerId` op `Account` (nullable? nee: elke user ís handler) | Jouw domeinkeuze; admin is óók handler |

## Wat expliciet NIET in deze slice zit
- Geen admin-sectie, geen activity/category-CRUD (volgende slice; trekt het schrijfmodel mee).
- Geen registratie-UI (het seed-script overbrugt tot de admin-sectie er is).
- Geen API-token-auth (de middleware is er klaar voor; JSON-401 zit erin, tokens later).

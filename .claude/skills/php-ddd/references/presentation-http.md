# De HTTP-laag als Ports & Adapters

De presentatielaag verdient dezelfde hexagonale discipline als het domein. Een
webframework is een *detail*: de kern van je applicatie mag er niet van afhangen.
Het leidende principe is **functional core, imperative shell** — de onzuivere
buitenwereld (superglobals, headers, output) leeft in één dunne schil; alles
daarbinnen is een pure functie van input naar output.

> Ubiquitaire taal geldt hier niet; dit is techniek. Maar de *afhankelijkheids­richting*
> (naar binnen) en *testbaarheid* zijn identiek aan de domeincanon.

## 1. Een controller-actie is een functie `Request → Response`

Geen superglobals, geen `echo`, geen `exit()`. Input komt binnen als argument,
output gaat als returnwaarde naar buiten. Zo is de actie unit-testbaar zonder
webserver en weet ze niets van de HTTP-machinerie.

```php
// FOUT — de controller ís de HTTP-laag
public function submit(): void
{
    $at = $_POST['checkInAt'];          // leest globale state
    $_SESSION['flash'] = [...];         // schrijft globale state
    header('Location: /'); exit();      // side-effect → ontestbaar
}

// GOED — pure functie; state komt binnen, gaat naar buiten
public function submit(ServerRequestInterface $request): ResponseInterface
{
    $body = (array) $request->getParsedBody();
    $this->session->flash('success', '...');     // via poort, niet $_SESSION
    return new Response(302, ['Location' => $redirectUrl]);
}
```

Het redirect wordt **data** (`302 + Location`), geen side-effect. De emitter (§4)
schrijft ze weg. Merk op: door een `Response` te *returnen* i.p.v. `exit()` te
roepen verdwijnt ook de klassieke "code na de redirect draait toch nog"-bug.

## 2. Superglobals enkel in de front controller

De front controller is de onzuivere schil: hij leest `$_GET`/`$_POST`/`$_SERVER`
**één keer** in tot een immutable PSR-7 request en geeft die door.

```php
$psr17 = new \Nyholm\Psr7\Factory\Psr17Factory();
$request = (new \Nyholm\Psr7Server\ServerRequestCreator($psr17, $psr17, $psr17, $psr17))
    ->fromGlobals();
```

Route-parameters (`/checkin/{id}`) reizen vanaf hier mee als **request-attributen**
(`$request->withAttribute('id', ...)`), niet als een los functieargument — dat is
het PSR-idioom en houdt de actiesignatuur uniform (`Request → Response`).

## 3. De sessie is een aparte poort — PSR-7 dekt ze niet

PSR-7 modelleert één request/response en zegt bewust niets over sessies (state
tússen requests). Zet de sessie dus achter een eigen interface. Dan blijft de
controller vrij van `$_SESSION` én testbaar met een in-memory implementatie.

```php
interface Session
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value): void;
    public function flash(string $type, string $message): void;
    /** @return array{type: string, message: string}|null */
    public function pullFlash(): ?array;   // read-once
}
```

Poort + adapter: `Session` (interface) ← `PhpSession` (over `$_SESSION`) in productie,
`InMemorySession` in tests. Dit is precies de winst die een batteries-included
component (bv. Symfony HttpFoundation met zijn `FlashBag`) *inbakt* — door de poort
zelf te bouwen begrijp je waaróm het een apart concern is.

## 4. Response-emitting op één plek

Precies wat een framework-"emitter" doet; door het één keer zelf te schrijven zie
je dat `header()`/`http_response_code()`/`echo` geen magie zijn maar de laatste,
geïsoleerde stap.

```php
final class ResponseEmitter
{
    public function emit(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            $replace = true;
            foreach ($values as $value) {
                header("{$name}: {$value}", $replace);
                $replace = false;   // eerste vervangt, rest voegt toe (Set-Cookie!)
            }
        }
        echo $response->getBody();
    }
}
```

## 5. De front controller bedraadt via de DI-container

Hij bouwt de request, laat de guard/router een `Response` bepalen, en emit die.
Dispatchlogica is deterministisch en leesbaar — **duidelijkheid boven slimme trucs**:

```php
if (Dispatcher::FOUND === $routeInfo[0]) {
    $controller = $container->get(CheckInController::class);
    foreach ($routeInfo[2] as $name => $value) {
        $request = $request->withAttribute($name, $value);
    }
    $response = match ($routeInfo[1]) { /* naam → actie($request) */ };
} elseif (Dispatcher::METHOD_NOT_ALLOWED === $routeInfo[0]) {
    $response = new Response(405);
} else {
    $response = new Response(404);
}
$emitter->emit($response);
```

De guard hoort thuis als (latere) **PSR-15 middleware**; tot dan is een pre-dispatch
check die een `Response(403)` teruggeeft prima.

## 6. Presentatie-formattering hoort niet op een domein-poort

Een `Translator` in `Domain/` levert vertalingen — dat is domeintaal. Een methode
als `getHtmlLang()` (die een HTML `lang`-attribuut opmaakt) is **presentatie** en
hoort dus níét op die domein-poort, ook al is het verleidelijk omdat de adapter de
data toevallig heeft. Leidt zulke formattering af in de presentatielaag uit de
locale. Test: *"zou het domein dit woord gebruiken?"* Nee → het is techniek/UI.

## Input-validatie: twee lagen, niet verwarren

"Validatie" is in DDD géén enkel ding maar **twee concerns op twee plekken**:

| | Waar | Wat | Bij overtreding |
| --- | --- | --- | --- |
| **Vorm-/inputvalidatie** | Presentatie (boundary) | Is de payload welgevormd? Verplichte velden, parseerbare datum, types | **Gebruikersfout** → nette 4xx/flash |
| **Domein-invarianten** | Domain (VO's + Aggregate) | Klopt de business-regel? | **Mag-niet-gebeuren** → domein-exceptie |

Het is **defense in depth**: de VO's valideren altijd in hun constructor (laatste
vangnet), en de boundary valideert de *vorm* vóór je de Command bouwt — zodat
rommelige input geen `TypeError` of domein-exceptie wordt. Gooi *niet* alles in het
domein (dan wordt "veld vergeten" een domein-exceptie), en maak het domein *niet*
anemisch door alles in de controller te proppen.

Praktisch: een **presentatie-input-object** (form) dat de raw array valideert
volgens de **Notification-pattern** — verzamel *álle* fouten i.p.v. te stoppen bij de
eerste — en óf een geldige Command óf een lijst fouten teruggeeft:

```php
$form = RecordCheckInForm::fromRequest((array) $request->getParsedBody());
if ($form->command === null) {
    // codes → tekst op de boundary (zie hieronder)
    return $this->redirectWithErrors($form->errors);
}
$this->handler->handle($form->command);   // domein bewaakt de rest
```

**Foutmeldingen zijn codes, geen mensentaal.** Het form geeft stabiele vertaal-codes
terug (`check_in.validation.handler_required`), niet "Kies een begeleider." Het
*verwoorden* gebeurt aan de rand met de `Translator`. Dit is géén laaggrens-kwestie
(het form ís presentatie), maar drie trade-offs: **formaat-onafhankelijkheid** (een
code wordt HTML-flash, JSON-`{field, code}`, of logregel), **Single Responsibility**
(valideren ≠ verwoorden), en **purity** (geen Translator/locale → puur, testbaar op
codes). Dupliceer de domeinregels niet in het form: het form checkt *vorm*, het
domein checkt *betekenis*.

> Merk het onderscheid op met §6: presentatie op een domein-poort is een **harde**
> afhankelijkheidsregel-schending; vertalen-in-het-form is slechts een **zwakkere
> trade-off** (verdedigbaar, maar codes winnen). Niet elk "houd X uit Y" is even hard.

## Smell: hoge constructor-ariteit = God-controller

Moet een test 9 collaborators optuigen om één actie te draaien, dan doet de
controller te veel. Splits in **single-action controllers** (één invokable klasse
per route); elke actie — en elke test — hangt dan enkel af van wat ze echt nodig heeft.

## Testwinst

Het hele punt van bovenstaande: een actie wordt een pure functie, dus een test is
`Request` erin → assert op de `Response`, met in-memory poorten. Geen webserver,
geen output-buffering, geen globale state.

```php
$response = $controller->submit($request->withParsedBody([...]));
self::assertSame(302, $response->getStatusCode());
self::assertSame('/', $response->getHeaderLine('Location'));
```

## Bibliotheekkeuze: "beter *waarvoor*?"

- **PSR-7 (bv. `nyholm/psr7`)** — standaard interfaces, interop met PSR-15-middleware,
  licht. Je plaatst sessie/emitter zelf → je leert de lagen. Vendor-neutraal.
- **Symfony HttpFoundation** — geen PSR-7 maar een rijke, ergonomische component met
  sessie/`FlashBag`/`Response::send()` ingebouwd. Minste eigen plumbing; bindt aan
  het Symfony-contract.

De vraag is nooit "welke is beter?" maar "beter *waarvoor*?": voor een leerproject
wint PSR-7 (de puzzel), voor snel opleveren wint HttpFoundation (de antwoorden).

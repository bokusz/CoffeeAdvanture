# sendOrder.php strukturált rendelési űrlap — Terv

**Dátum:** 2026-08-03
**Érintett fájlok:** `sendOrder.php`, `successfulOrder.php` (új), `index.php`

## Cél

A `sendOrder.php` egyetlen szabad szöveges textarea helyett strukturált, blokkokra bontott rendelési űrlapot kap, mennyiségi legördülőkkel, szállítási és megrendelői adatokkal. Sikeres beküldésre e-mail megy a rendelésről, és a felhasználó egy `successfulOrder.php` visszaigazoló oldalra kerül. Az `index.php` rendelés-szekciója egy űrlapra átvezető gombra egyszerűsödik. A hozzáférési `key` URL-paraméter minden belső navigáción továbbvándorol.

## Menü adatstruktúra

A termékek egy PHP tömbben, a fájl tetején definiálva, hogy az űrlap és a validáció ugyanabból dolgozzon:

**Sütemények:**
- Brownie — mennyiség 0–5, alap 0
- Chocolate Chip Cookie — 0–5, alap 0
- Csokis pöffeteg — 0–5, alap 0

**Italok:**
- Limonádé — 0–3, alap 0
- Jegeskávé — 0–2, alap 0
- Tejeskávé — 0–2, alap 0

## Űrlap blokkok (egymás alatt, `.order-box` stílusú blokkok)

### 1. Sütemények
Három sor, mindegyikben felirat + legördülő (`<select>`). Alap 0.

### 2. Italok
Három sor, felirat + legördülő. Alap 0.

### 3. Szállítási adatok
- **Mikorra:** `datetime-local` mező. `min` = most + 30 perc. Alapérték = most + 30 perc, felfelé kerekítve a következő kerek vagy fél órára. Kötelező.
- **Utca:** legördülő, ABC-sorrendben: József Attila utca, Kisperrjési utca, Malomvölgy utca, Szegedi Róza utca, Táncsics Mihály utca. Alapértelmezett kiválasztás: Malomvölgy utca (`selected`, a sorrend marad ABC).
- **Házszám:** `number` mező, 1–100. Alap 0. Kötelező (a 0 alapérték érvénytelen → változtatás nélkül hibát ad).

### 4. Megrendelő adatai (opcionális)
- Tájékoztató szöveg: „Megrendelő adatai opcionális, de ha e-mail címet vagy telefonszámot megadsz, tudunk értesíteni, ha bármilyen kérdés, probléma felmerül a megrendeléseddel kapcsolatban."
- **E-mail (opcionális):** szöveges mező, alap üres.
- **Telefonszám (opcionális):** szöveges mező, alap üres.

### 5. Megjegyzés (opcionális)
Textarea, ~4 sor magas, alap üres.

Alul: **Rendelés küldése** gomb.

## Validáció (szerveroldali PHP, böngésző-oldali `min`/`required`/`max` kiegészítéssel)

- **Mikorra:** kötelező, és `>=` most + 30 perc. Böngésző `min` + PHP újraellenőrzés (megkerülés ellen).
- **Utca:** mindig van érvényes értéke (a lista egyike).
- **Házszám:** kötelező, egész, 1–100 tartományban.
- **Mennyiségek:** 0 is érvényes, de a termékenkénti megengedett tartományon belül.
- **Legalább egy tétel:** a sütemények + italok összmennyisége `>= 1`, különben hiba.
- **E-mail:** ha meg van adva, formátum-ellenőrzés (`filter_var`). Ha hibás → az e-mail a többi címzettnek **akkor is** elmegy, csak a CC marad ki. Ha üres → nincs CC.

## E-mail generálás és küldés (PHPMailer, meglévő SMTP config)

- **Rendelés azonosító:** 4 karakteres véletlenszerű alfanumerikus kód, a POST feldolgozásakor generálva.
- **Címzettek:**
  - To: `zoebaloght@gmail.com`
  - BCC: `gabor.boka.jr@gmail.com`, `boglarka.boka@gmail.com`, `biborka.boka@gmail.com`, `bokatibor.old@gmail.com`
  - CC: az E-mail (opcionális) mező értéke, csak ha kitöltött és érvényes formátumú.
- **Tárgy:** `Kávékaland - Megrendelés - <azonosító>`
- **Törzs:** szépen formázott szöveg — azonosító, majd blokkonként a megrendelt tételek (csak a > 0 mennyiségűek felsorolva), szállítási adatok, megrendelő adatai (ha van), megjegyzés (ha van).

## Sikeres beküldés → successfulOrder.php

- Siker esetén a rendelés részletei (az e-maillel azonos tartalom) **PHP session**-be kerülnek, majd `header('Location: successfulOrder.php?key=...')` átirányítás. A session megakadályozza, hogy a részletek az URL-be kerüljenek, és hogy frissítés újraküldje a rendelést.
- **successfulOrder.php** (új fájl): a sendOrder.php stílusával azonos (közös `style.css` + azonos inline stílusblokk). Középen köszönő szöveg: „Rendelésedet köszönjük! Feldolgozását megkezdtük. Ha kérdésünk felmerül, keressük, egyéb esteben a megadott időre szállítunk. Bármilyen kérédése felmerül a zoebaloght@gmail.com e-mail címen elér minket." Alatta egy blokkban a rendelés részletei. A session-adatot kiolvasás után törli. Ha nincs session-adat (közvetlen hívás) → visszairányít a sendOrder.php-ra.

## Hibakezelés

Bármely hiba (validáció vagy e-mail küldés) esetén a felhasználó **marad** a sendOrder.php-n, a hibaüzenet megjelenik az oldalon, és a már kitöltött mezőértékek megmaradnak (a POST értékekből visszatöltve).

## index.php rendelés-szekció csere

A `<section class="order" id="order">` belseje lecserélődik:
- Szöveg: „A megrendelését a következő űrlapon tudja megtenni:"
- „Rendelés feladása" gomb → `sendOrder.php?key=<?= $key ?>`

## Key továbbvitele

Az első híváskor megadott `key` minden belső linken és űrlap-action-ön továbbvándorol: index.php gomb → sendOrder.php, sendOrder.php form action, redirect a successfulOrder.php-ra, minden navbar link. **Kivétel:** az unauthorized.php-ra sosem visszük a key-t. A sendOrder.php és a successfulOrder.php ugyanazt a `$allowed` kulcs-ellenőrzést végzi, mint az index.php.

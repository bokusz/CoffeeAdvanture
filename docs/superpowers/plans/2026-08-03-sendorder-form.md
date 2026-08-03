# Strukturált sendOrder rendelési űrlap — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A `sendOrder.php` egyetlen textarea helyett strukturált, blokkos rendelési űrlapot kap mennyiségi legördülőkkel, szállítási és megrendelői adatokkal; sikeres beküldésre e-mail megy és a felhasználó egy `successfulOrder.php` visszaigazoló oldalra kerül; az `index.php` rendelés-szekció egy űrlapra átvezető gombra egyszerűsödik.

**Architecture:** Egy közös `order_menu.php` definiálja a menü- és utca-adatstruktúrát meg a segédfüggvényeket (ID-generálás, tétel-összesítés, e-mail törzs építése), amit a `sendOrder.php` és a `successfulOrder.php` is használ (DRY). A `sendOrder.php` végzi a form-megjelenítést, szerveroldali validációt és PHPMailer-küldést; sikerkor session-be teszi a rendelés részleteit és átirányít. A `successfulOrder.php` a session-ből olvassa és megjeleníti a részleteket, majd törli.

**Tech Stack:** PHP (procedurális), PHPMailer (SMTP, meglévő `smtp_config.php`), közös `style.css`, PHP session. Nincs test framework — a verifikáció `php -l` szintaxis-ellenőrzés + manuális checklista.

## Global Constraints

- Nyelv: magyar felhasználói szövegek, UTF-8.
- Kulcs-ellenőrzés minden védett oldalon: `$allowed=['bf4Gde67f','ndskFCgfj23','mjlzbtk3Den','df56hdfFV3','nzkj57nf2h'];` — hiányzó/érvénytelen `key` esetén `header('Location: unauthorized.php'); exit;`.
- A `key` URL-paraméter minden belső linken és redirecten továbbvándorol; az `unauthorized.php`-ra SOSEM.
- Minden felhasználói bemenet HTML-kimenetnél `htmlspecialchars`-olva.
- Menü és max mennyiségek: Brownie 0–5, Chocolate Chip Cookie 0–5, Csokis pöffeteg 0–5, Limonádé 0–3, Jegeskávé 0–2, Tejeskávé 0–2.
- Utcák ABC-sorrendben: József Attila utca, Kisperrjési utca, Malomvölgy utca, Szegedi Róza utca, Táncsics Mihály utca. Alapértelmezett: Malomvölgy utca.
- E-mail címzettek — To: `zoebaloght@gmail.com`; BCC: `gabor.boka.jr@gmail.com`, `boglarka.boka@gmail.com`, `biborka.boka@gmail.com`, `bokatibor.old@gmail.com`; CC: az opcionális e-mail mező, ha kitöltött és érvényes.
- Tárgy: `Kávékaland - Megrendelés - <azonosító>`.
- PHP futtatás verifikációhoz: `/c/JavaPrograms/php/php` (Git Bash útvonal).

---

## File Structure

- **Create `order_menu.php`** — menü- és utca-adat + tiszta segédfüggvények (nincs I/O, nincs kimenet). Egységteszthető `php -r`-rel.
- **Modify `sendOrder.php`** — teljes átírás: form, validáció, e-mail, session, redirect.
- **Create `successfulOrder.php`** — session-ből visszaigazoló oldal.
- **Modify `index.php`** — a `<section class="order" id="order">` belsejének cseréje.

---

### Task 1: Közös menü- és segédmodul (`order_menu.php`)

Tiszta adat + függvények, amiket a sendOrder és a successfulOrder is használ. Nincs benne kimenet vagy header, hogy include-olható legyen redirect előtt is.

**Files:**
- Create: `order_menu.php`

**Interfaces:**
- Produces:
  - `ca_menu(): array` — visszaad egy tömböt két csoporttal. Struktúra:
    `['Sütemények' => ['brownie'=>['label'=>'Brownie','max'=>5], 'cookie'=>['label'=>'Chocolate Chip Cookie','max'=>5], 'poffeteg'=>['label'=>'Csokis pöffeteg','max'=>5]], 'Italok' => ['limonade'=>['label'=>'Limonádé','max'=>3], 'jegeskave'=>['label'=>'Jegeskávé','max'=>2], 'tejeskave'=>['label'=>'Tejeskávé','max'=>2]]]`
  - `ca_streets(): array` — ABC-sorrendű utcanevek tömbje (5 elem).
  - `ca_order_id(): string` — 4 karakteres alfanumerikus azonosító `[A-Z0-9]`.
  - `ca_collect_quantities(array $post, array $menu): array` — minden termékkulcshoz egész mennyiséget ad vissza a tartományra szorítva (`0..max`); a hiányzó vagy nem-numerikus érték 0. Visszatérés: `['brownie'=>2, ...]` minden kulcsra.
  - `ca_total_items(array $qty): int` — a mennyiségek összege.
  - `ca_build_body(string $orderId, array $qty, array $menu, string $when, string $street, string $houseNo, string $email, string $phone, string $note): string` — a formázott e-mail/visszaigazolás törzse. Csak a `>0` mennyiségű tételek jelennek meg, csoportcímek alatt; üres opcionális mezők kimaradnak.

- [ ] **Step 1: Írd meg az `order_menu.php` fájlt**

```php
<?php
// Közös menü-, utca- és segédfüggvények a rendelési űrlaphoz.
// Nincs kimenet és nincs header itt — include-olható redirect előtt is.

function ca_menu(): array {
    return [
        'Sütemények' => [
            'brownie'  => ['label' => 'Brownie', 'max' => 5],
            'cookie'   => ['label' => 'Chocolate Chip Cookie', 'max' => 5],
            'poffeteg' => ['label' => 'Csokis pöffeteg', 'max' => 5],
        ],
        'Italok' => [
            'limonade'  => ['label' => 'Limonádé', 'max' => 3],
            'jegeskave' => ['label' => 'Jegeskávé', 'max' => 2],
            'tejeskave' => ['label' => 'Tejeskávé', 'max' => 2],
        ],
    ];
}

function ca_streets(): array {
    // ABC-sorrend
    return [
        'József Attila utca',
        'Kisperrjési utca',
        'Malomvölgy utca',
        'Szegedi Róza utca',
        'Táncsics Mihály utca',
    ];
}

function ca_order_id(): string {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id = '';
    for ($i = 0; $i < 4; $i++) {
        $id .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $id;
}

function ca_collect_quantities(array $post, array $menu): array {
    $qty = [];
    foreach ($menu as $group => $items) {
        foreach ($items as $key => $meta) {
            $raw = isset($post[$key]) ? $post[$key] : 0;
            $n = is_numeric($raw) ? (int) $raw : 0;
            if ($n < 0) {
                $n = 0;
            }
            if ($n > $meta['max']) {
                $n = $meta['max'];
            }
            $qty[$key] = $n;
        }
    }
    return $qty;
}

function ca_total_items(array $qty): int {
    $sum = 0;
    foreach ($qty as $n) {
        $sum += (int) $n;
    }
    return $sum;
}

function ca_build_body(string $orderId, array $qty, array $menu, string $when, string $street, string $houseNo, string $email, string $phone, string $note): string {
    $lines = [];
    $lines[] = 'Megrendelés azonosító: ' . $orderId;
    $lines[] = '';

    foreach ($menu as $group => $items) {
        $groupLines = [];
        foreach ($items as $key => $meta) {
            $n = isset($qty[$key]) ? (int) $qty[$key] : 0;
            if ($n > 0) {
                $groupLines[] = '  - ' . $meta['label'] . ': ' . $n . ' db';
            }
        }
        if ($groupLines) {
            $lines[] = $group . ':';
            foreach ($groupLines as $gl) {
                $lines[] = $gl;
            }
            $lines[] = '';
        }
    }

    $lines[] = 'Szállítási adatok:';
    $lines[] = '  Mikorra: ' . $when;
    $lines[] = '  Cím: ' . $street . ' ' . $houseNo . '.';
    $lines[] = '';

    if ($email !== '' || $phone !== '') {
        $lines[] = 'Megrendelő adatai:';
        if ($email !== '') {
            $lines[] = '  E-mail: ' . $email;
        }
        if ($phone !== '') {
            $lines[] = '  Telefonszám: ' . $phone;
        }
        $lines[] = '';
    }

    if ($note !== '') {
        $lines[] = 'Megjegyzés:';
        $lines[] = '  ' . $note;
        $lines[] = '';
    }

    return implode("\n", $lines);
}
```

- [ ] **Step 2: Szintaxis-ellenőrzés**

Run: `/c/JavaPrograms/php/php -l order_menu.php`
Expected: `No syntax errors detected in order_menu.php`

- [ ] **Step 3: Függvények füstteszt `php -r`-rel**

Run:
```bash
/c/JavaPrograms/php/php -r 'require "order_menu.php"; $m=ca_menu(); $q=ca_collect_quantities(["brownie"=>"9","limonade"=>"2","cookie"=>"-1"],$m); echo "brownie=".$q["brownie"]." limonade=".$q["limonade"]." cookie=".$q["cookie"]." total=".ca_total_items($q)."\n"; echo "id_len=".strlen(ca_order_id())."\n"; echo "streets=".count(ca_streets())."\n"; echo ca_build_body("AB12",$q,$m,"2026-08-03 14:00","Malomvölgy utca","5","","","Kérlek csengess")."\n";'
```
Expected (kulcs-ellenőrzések): `brownie=5` (9 levágva max 5-re), `limonade=2`, `cookie=0` (negatív → 0), `total=7`, `id_len=4`, `streets=5`, és a törzsben megjelenik `Brownie: 5 db`, `Limonádé: 2 db`, a `Cookie` NEM (0 db), valamint a `Megjegyzés:` blokk, de NINCS `Megrendelő adatai` blokk (üres email+telefon).

- [ ] **Step 4: Commit**

```bash
git add order_menu.php
git commit -m "Add shared order menu data and helpers (order_menu.php)"
```

---

### Task 2: sendOrder.php — form megjelenítés és validáció (e-mail nélkül)

Átírjuk a `sendOrder.php`-t: kulcs-ellenőrzés, session start, a form kirajzolása a menüből, szerveroldali validáció, hibaüzenet + értékmegőrzés. Az e-mail-küldést a Task 3 adja hozzá — ebben a taskban a POST validációja után egyelőre csak összegyűjtjük a hibákat és (hiba hiányában) NEM küldünk, hanem a `$readyToSend=true` jelzőt állítjuk.

**Files:**
- Modify: `sendOrder.php` (teljes csere)

**Interfaces:**
- Consumes: `order_menu.php` összes függvénye.
- Produces: a POST-feldolgozás végén `$readyToSend` (bool), `$orderId` (string), `$qty` (array), `$when`,`$street`,`$houseNo`,`$email`,`$phone`,`$note` (string) változók — ezekre épít a Task 3.

- [ ] **Step 1: Írd meg a `sendOrder.php` fájlt (form + validáció, küldés nélkül)**

```php
<?php
session_start();

$allowed = ['bf4Gde67f','ndskFCgfj23','mjlzbtk3Den','df56hdfFV3','nzkj57nf2h'];
if (!isset($_GET['key']) || !in_array($_GET['key'], $allowed, true)) {
    header('Location: unauthorized.php');
    exit;
}

require_once 'order_menu.php';

$key    = $_GET['key'];
$keyEnc = htmlspecialchars($key);
$menu   = ca_menu();
$streets = ca_streets();

$errors = [];

// A "Mikorra" minimum értéke: most + 30 perc, HTML datetime-local formátumban (Y-m-dTH:i).
$minTs  = time() + 30 * 60;
$minAttr = date('Y-m-d\TH:i', $minTs);

// Alapérték: most + 30 perc, felkerekítve a következő kerek vagy fél órára.
$defaultTs = $minTs;
$mins = (int) date('i', $defaultTs);
if ($mins === 0 || $mins === 30) {
    $roundedTs = $defaultTs - ((int) date('s', $defaultTs));
} elseif ($mins < 30) {
    $roundedTs = mktime((int) date('H', $defaultTs), 30, 0, (int) date('n', $defaultTs), (int) date('j', $defaultTs), (int) date('Y', $defaultTs));
} else {
    $roundedTs = mktime((int) date('H', $defaultTs) + 1, 0, 0, (int) date('n', $defaultTs), (int) date('j', $defaultTs), (int) date('Y', $defaultTs));
}
$defaultWhen = date('Y-m-d\TH:i', $roundedTs);

// Form értékek (POST visszatöltéshez).
$qty      = array_fill_keys(array_keys(array_merge($menu['Sütemények'], $menu['Italok'])), 0);
$when     = $defaultWhen;
$street   = 'Malomvölgy utca';
$houseNo  = '';
$email    = '';
$phone    = '';
$note     = '';

$readyToSend = false;
$orderId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty     = ca_collect_quantities($_POST, $menu);
    $when    = isset($_POST['when']) ? trim($_POST['when']) : '';
    $street  = isset($_POST['street']) ? trim($_POST['street']) : '';
    $houseNo = isset($_POST['house_no']) ? trim($_POST['house_no']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $note    = isset($_POST['note']) ? trim($_POST['note']) : '';

    // Legalább egy tétel.
    if (ca_total_items($qty) < 1) {
        $errors[] = 'Legalább egy terméket ki kell választanod.';
    }

    // Mikorra: kötelező és >= most + 30 perc.
    $whenTs = strtotime($when);
    if ($when === '' || $whenTs === false) {
        $errors[] = 'A szállítási időpont megadása kötelező.';
    } elseif ($whenTs < $minTs) {
        $errors[] = 'A szállítási időpont legalább 30 perccel a mostani időpont után legyen.';
    }

    // Utca: érvényes érték a listából.
    if (!in_array($street, $streets, true)) {
        $errors[] = 'Érvénytelen utca.';
    }

    // Házszám: egész 1..100.
    if ($houseNo === '' || !ctype_digit($houseNo) || (int) $houseNo < 1 || (int) $houseNo > 100) {
        $errors[] = 'A házszám 1 és 100 közötti szám legyen.';
    }

    if (!$errors) {
        $orderId = ca_order_id();
        $readyToSend = true;
        // Az e-mail-küldést a következő lépés adja hozzá.
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KávéKaland | Rendelés leadása</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .send-order-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .order-form-wrap {
            width: 100%;
            max-width: 640px;
        }
        .order-block {
            background: rgba(255,255,255,.06);
            border-radius: 20px;
            padding: 22px 26px;
            margin: 18px 0;
        }
        .order-block h4 {
            margin: 0 0 14px;
            color: #FFEDA8;
            font-size: 18px;
        }
        .order-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 10px 0;
        }
        .order-row label {
            flex: 1;
            color: #fff;
            font-weight: 500;
        }
        .order-row select,
        .order-row input {
            padding: 10px 14px;
            border-radius: 12px;
            border: none;
            font-family: "Poppins", sans-serif;
            font-size: 15px;
            color: #1d2652;
        }
        .order-block textarea {
            width: 100%;
            min-height: 96px;
            padding: 14px;
            border-radius: 14px;
            border: none;
            font-family: "Poppins", sans-serif;
            font-size: 15px;
            color: #1d2652;
            resize: vertical;
        }
        .order-block .hint {
            color: #dfe6ff;
            font-size: 14px;
            margin: 0 0 12px;
        }
        .send-btn {
            display: inline-block;
            padding: 16px 35px;
            border-radius: 50px;
            border: none;
            background: #FFEDA8;
            color: #154061;
            font-family: "Poppins", sans-serif;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: .3s;
            box-shadow: 0 10px 25px rgba(255,237,168,.4);
        }
        .send-btn:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 20px 35px rgba(255,237,168,.5);
        }
        .msg-error {
            margin: 0 0 18px;
            padding: 14px 18px;
            background: rgba(255,120,120,.15);
            border-radius: 14px;
            color: #ffd0d0;
            font-weight: 600;
        }
        .msg-error ul { margin: 8px 0 0; padding-left: 20px; }
    </style>
</head>
<body>

    <header>
        <nav class="navbar">
            <div class="logo">KávéKaland</div>
            <ul>
                <li><a href="index.php?key=<?= $keyEnc ?>">Kezdőlap</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#products">Termékek</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#team">Csapat</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#order">Rendelés info</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#contact">Kapcsolat</a></li>
            </ul>
        </nav>
    </header>

    <section class="order send-order-section">
        <div class="order-form-wrap">
            <h3>Rendelés leadása</h3>

            <?php if ($errors): ?>
                <div class="msg-error">
                    Kérjük javítsd a következőket:
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="sendOrder.php?key=<?= $keyEnc ?>">

                <?php foreach ($menu as $groupName => $items): ?>
                    <div class="order-block">
                        <h4><?= htmlspecialchars($groupName) ?></h4>
                        <?php foreach ($items as $pkey => $meta): ?>
                            <div class="order-row">
                                <label for="<?= $pkey ?>"><?= htmlspecialchars($meta['label']) ?>:</label>
                                <select name="<?= $pkey ?>" id="<?= $pkey ?>">
                                    <?php for ($i = 0; $i <= $meta['max']; $i++): ?>
                                        <option value="<?= $i ?>" <?= ((int) $qty[$pkey] === $i) ? 'selected' : '' ?>><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div class="order-block">
                    <h4>Szállítási adatok</h4>
                    <div class="order-row">
                        <label for="when">Mikorra:</label>
                        <input type="datetime-local" name="when" id="when"
                               min="<?= htmlspecialchars($minAttr) ?>"
                               value="<?= htmlspecialchars($when) ?>" required>
                    </div>
                    <div class="order-row">
                        <label for="street">Utca:</label>
                        <select name="street" id="street">
                            <?php foreach ($streets as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>" <?= ($s === $street) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="order-row">
                        <label for="house_no">Házszám:</label>
                        <input type="number" name="house_no" id="house_no" min="1" max="100"
                               value="<?= htmlspecialchars($houseNo) ?>" required>
                    </div>
                </div>

                <div class="order-block">
                    <h4>Megrendelő adatai (opcionális)</h4>
                    <p class="hint">Megrendelő adatai opcionális, de ha e-mail címet vagy telefonszámot megadsz, tudunk értesíteni, ha bármilyen kérdés, probléma felmerül a megrendeléseddel kapcsolatban.</p>
                    <div class="order-row">
                        <label for="email">E-mail (opcionális):</label>
                        <input type="text" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
                    </div>
                    <div class="order-row">
                        <label for="phone">Telefonszám (opcionális):</label>
                        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>">
                    </div>
                </div>

                <div class="order-block">
                    <h4>Megjegyzés (opcionális)</h4>
                    <textarea name="note" rows="4"><?= htmlspecialchars($note) ?></textarea>
                </div>

                <button type="submit" class="send-btn">Rendelés küldése</button>
            </form>
        </div>
    </section>

</body>
</html>
```

- [ ] **Step 2: Szintaxis-ellenőrzés**

Run: `/c/JavaPrograms/php/php -l sendOrder.php`
Expected: `No syntax errors detected in sendOrder.php`

- [ ] **Step 3: Beépített szerverrel form-renderelés ellenőrzése**

Run:
```bash
/c/JavaPrograms/php/php -S 127.0.0.1:8765 >/tmp/php_srv.log 2>&1 &
sleep 1
curl -s "http://127.0.0.1:8765/sendOrder.php?key=bf4Gde67f" | grep -c -E 'name="brownie"|name="when"|name="street"|name="house_no"|name="email"|name="phone"|name="note"'
curl -s -o /dev/null -w "%{http_code}\n" "http://127.0.0.1:8765/sendOrder.php"
kill %1 2>/dev/null
```
Expected: az első `curl` `7`-et ad (mind a 7 mező jelen van); a második (key nélkül) `302` (redirect az unauthorized-ra).

- [ ] **Step 4: Commit**

```bash
git add sendOrder.php
git commit -m "Rewrite sendOrder.php with structured form and server-side validation"
```

---

### Task 3: sendOrder.php — e-mail küldés, session, redirect

A validáció utáni `$readyToSend` ágba beépítjük a PHPMailer-küldést, session-mentést és redirectet.

**Files:**
- Modify: `sendOrder.php` (a `if (!$errors) { ... }` blokk kibővítése + PHPMailer use-ok)

**Interfaces:**
- Consumes: `$readyToSend`, `$orderId`, `$qty`, `$menu`, `$when`, `$street`, `$houseNo`, `$email`, `$phone`, `$note` a Task 2-ből; `ca_build_body()` a Task 1-ből.
- Produces: `$_SESSION['ca_order']` tömb a successfulOrder.php számára, kulcsok: `id`, `body`.

- [ ] **Step 1: Add hozzá a PHPMailer `use` sorokat a fájl legelejére**

A `<?php` után, a `session_start();` ELŐTT:

```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
```

- [ ] **Step 2: Cseréld le a `if (!$errors) { ... }` blokkot a küldő logikára**

Keresd meg:

```php
    if (!$errors) {
        $orderId = ca_order_id();
        $readyToSend = true;
        // Az e-mail-küldést a következő lépés adja hozzá.
    }
```

Cseréld erre:

```php
    if (!$errors) {
        $orderId = ca_order_id();
        $body = ca_build_body($orderId, $qty, $menu, $when, $street, $houseNo, $email, $phone, $note);

        require_once 'smtp_config.php';
        require_once 'PHPMailer/Exception.php';
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(SMTP_USER, 'KávéKaland');
            $mail->addAddress('zoebaloght@gmail.com');
            $mail->addBCC('gabor.boka.jr@gmail.com');
            $mail->addBCC('boglarka.boka@gmail.com');
            $mail->addBCC('biborka.boka@gmail.com');
            $mail->addBCC('bokatibor.old@gmail.com');

            // CC csak akkor, ha az opcionális e-mail kitöltött ÉS érvényes.
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addCC($email);
            }

            $mail->Subject = 'Kávékaland - Megrendelés - ' . $orderId;
            $mail->Body    = $body;

            $mail->send();

            $_SESSION['ca_order'] = ['id' => $orderId, 'body' => $body];
            header('Location: successfulOrder.php?key=' . urlencode($key));
            exit;
        } catch (Exception $e) {
            $errors[] = 'Hiba az e-mail küldésekor: ' . $mail->ErrorInfo;
        }
    }
```

- [ ] **Step 3: Szintaxis-ellenőrzés**

Run: `/c/JavaPrograms/php/php -l sendOrder.php`
Expected: `No syntax errors detected in sendOrder.php`

- [ ] **Step 4: Verifikáció — a küldő ág forráskódja helyes**

Run:
```bash
grep -c -E "addAddress\('zoebaloght@gmail.com'\)|addBCC\('gabor.boka.jr@gmail.com'\)|addBCC\('boglarka.boka@gmail.com'\)|addBCC\('biborka.boka@gmail.com'\)|addBCC\('bokatibor.old@gmail.com'\)|Kávékaland - Megrendelés - |successfulOrder.php\?key=" sendOrder.php
```
Expected: `7` (mind a 7 minta megvan: 1 To + 4 BCC + tárgy + redirect).

Megjegyzés: valódi SMTP-küldést nem tesztelünk automatikusan (kifelé menő levél); ez a manuális, éles ellenőrzés tárgya a záró taskban.

- [ ] **Step 5: Commit**

```bash
git add sendOrder.php
git commit -m "Add PHPMailer send, session save and redirect to sendOrder.php"
```

---

### Task 4: successfulOrder.php visszaigazoló oldal

Session-ből olvassa a rendelés részleteit, megjeleníti, majd törli. Ha nincs session-adat → vissza a sendOrder.php-ra.

**Files:**
- Create: `successfulOrder.php`

**Interfaces:**
- Consumes: `$_SESSION['ca_order']` (`id`, `body`) a Task 3-ból.

- [ ] **Step 1: Írd meg a `successfulOrder.php` fájlt**

```php
<?php
session_start();

$allowed = ['bf4Gde67f','ndskFCgfj23','mjlzbtk3Den','df56hdfFV3','nzkj57nf2h'];
if (!isset($_GET['key']) || !in_array($_GET['key'], $allowed, true)) {
    header('Location: unauthorized.php');
    exit;
}

$key    = $_GET['key'];
$keyEnc = htmlspecialchars($key);

// Rendelés részletei csak a session-ből; közvetlen hívásnál vissza az űrlapra.
if (empty($_SESSION['ca_order'])) {
    header('Location: sendOrder.php?key=' . urlencode($key));
    exit;
}

$order = $_SESSION['ca_order'];
unset($_SESSION['ca_order']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KávéKaland | Rendelés elküldve</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .send-order-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .success-wrap {
            width: 100%;
            max-width: 640px;
            text-align: center;
        }
        .success-msg {
            color: #fff;
            font-size: 19px;
            font-weight: 600;
            line-height: 1.5;
            margin-bottom: 28px;
        }
        .order-details {
            background: rgba(255,255,255,.06);
            border-radius: 20px;
            padding: 22px 26px;
            text-align: left;
            color: #fff;
            white-space: pre-wrap;
            font-size: 15px;
            line-height: 1.5;
        }
        .order-details h4 {
            margin: 0 0 14px;
            color: #FFEDA8;
            font-size: 18px;
        }
    </style>
</head>
<body>

    <header>
        <nav class="navbar">
            <div class="logo">KávéKaland</div>
            <ul>
                <li><a href="index.php?key=<?= $keyEnc ?>">Kezdőlap</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#products">Termékek</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#team">Csapat</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#order">Rendelés info</a></li>
                <li><a href="index.php?key=<?= $keyEnc ?>#contact">Kapcsolat</a></li>
            </ul>
        </nav>
    </header>

    <section class="order send-order-section">
        <div class="success-wrap">
            <p class="success-msg">Rendelésedet köszönjük! Feldolgozását megkezdtük. Ha kérdésünk felmerül, keressük, egyéb esteben a megadott időre szállítunk. Bármilyen kérédése felmerül a zoebaloght@gmail.com e-mail címen elér minket.</p>
            <div class="order-details">
                <h4>Rendelés részletei</h4><?= htmlspecialchars($order['body']) ?>
            </div>
        </div>
    </section>

</body>
</html>
```

- [ ] **Step 2: Szintaxis-ellenőrzés**

Run: `/c/JavaPrograms/php/php -l successfulOrder.php`
Expected: `No syntax errors detected in successfulOrder.php`

- [ ] **Step 3: Verifikáció — session-védelem és key-ellenőrzés**

Run:
```bash
/c/JavaPrograms/php/php -S 127.0.0.1:8766 >/tmp/php_srv2.log 2>&1 &
sleep 1
# key nélkül -> 302 unauthorized
curl -s -o /dev/null -w "no_key=%{http_code}\n" "http://127.0.0.1:8766/successfulOrder.php"
# érvényes key, de nincs session -> 302 vissza a sendOrder-re
curl -s -o /dev/null -w "no_session=%{http_code} -> %{redirect_url}\n" "http://127.0.0.1:8766/successfulOrder.php?key=bf4Gde67f"
kill %1 2>/dev/null
```
Expected: `no_key=302`; `no_session=302 -> ...sendOrder.php?key=bf4Gde67f`.

- [ ] **Step 4: Commit**

```bash
git add successfulOrder.php
git commit -m "Add successfulOrder.php confirmation page (session-based)"
```

---

### Task 5: index.php rendelés-szekció csere

A `<section class="order" id="order">` belsejét lecseréljük egy rövid szövegre + „Rendelés feladása" gombra, ami a `key`-t továbbviszi.

**Files:**
- Modify: `index.php` (a `<section class="order" id="order">` blokk tartalma)

**Interfaces:**
- Consumes: `$key` az index.php tetején már definiált változó. Ellenőrizd, hogy létezik-e; ha az index.php csak `$_GET['key']`-t használ inline, akkor a gomblinkben `htmlspecialchars($_GET['key'])`-t használj.

- [ ] **Step 1: Ellenőrizd, hogyan érhető el a key az index.php-ban**

Run: `grep -n "\$key\|\$_GET\['key'\]" index.php`
Expected: látszik, hogy van-e `$key` változó. A cserében a meglévő mintát kövesd (ha nincs `$key`, definiáld a szekció előtt vagy használj `htmlspecialchars($_GET['key'])`-t a linkben).

- [ ] **Step 2: Cseréld le a rendelés-szekció teljes tartalmát**

Keresd meg a `<section class="order" id="order">` nyitótagtól a hozzá tartozó `</section>` zárótagig tartó blokkot, és a belsejét (a `<section ...>` és `</section>` között) cseréld erre:

```php
        <h2>
            Hogyan rendelj?
        </h2>
        <div class="order-box">
            <p>A megrendelését a következő űrlapon tudja megtenni:</p>
            <a class="order-cta-btn" href="sendOrder.php?key=<?= htmlspecialchars($_GET['key']) ?>">Rendelés feladása</a>
        </div>
```

- [ ] **Step 3: Adj hozzá gomb-stílust az index.php `<style>` blokkjához (ha még nincs `.order-cta-btn`)**

Run: `grep -n "order-cta-btn" index.php`
Ha nincs találat, keresd meg az index.php `<style>` záró `</style>` tagját, és elé szúrd be:

```css
.order-cta-btn {
    display: inline-block;
    margin-top: 16px;
    padding: 16px 35px;
    border-radius: 50px;
    background: #FFEDA8;
    color: #154061;
    text-decoration: none;
    font-weight: 800;
    transition: .3s;
    box-shadow: 0 10px 25px rgba(255,237,168,.4);
}
.order-cta-btn:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 20px 35px rgba(255,237,168,.5);
}
```

Ha az index.php nem tartalmaz inline `<style>`-t (minden a style.css-ben van), akkor a fenti szabályokat a `style.css` végére add hozzá helyette.

- [ ] **Step 4: Szintaxis-ellenőrzés**

Run: `/c/JavaPrograms/php/php -l index.php`
Expected: `No syntax errors detected in index.php`

- [ ] **Step 5: Verifikáció — a gomb és a key jelen van**

Run:
```bash
/c/JavaPrograms/php/php -S 127.0.0.1:8767 >/tmp/php_srv3.log 2>&1 &
sleep 1
curl -s "http://127.0.0.1:8767/index.php?key=bf4Gde67f" | grep -c 'sendOrder.php?key=bf4Gde67f'
curl -s "http://127.0.0.1:8767/index.php?key=bf4Gde67f" | grep -c 'A megrendelését a következő űrlapon'
kill %1 2>/dev/null
```
Expected: mindkét `grep` `>= 1` (a gomblink a key-jel és az új szöveg is jelen van).

- [ ] **Step 6: Commit**

```bash
git add index.php style.css
git commit -m "Replace index.php order section with form CTA button carrying key"
```

---

### Task 6: Végső integrációs és éles-küldés ellenőrzés (manuális)

Ez a task nem ír kódot; a teljes folyamatot igazolja, beleértve a valódi e-mail-küldést (kifelé menő levél — csak élesben/valós SMTP-vel tesztelhető).

**Files:** nincs (verifikáció).

- [ ] **Step 1: Teljes folyamat helyi szerveren**

Run:
```bash
/c/JavaPrograms/php/php -S 127.0.0.1:8768 >/tmp/php_srv4.log 2>&1 &
sleep 1
echo "Nyisd meg: http://127.0.0.1:8768/index.php?key=bf4Gde67f"
```
Manuális checklista a böngészőben:
1. index.php → „Rendelés feladása" gomb átvisz a sendOrder.php-ra, az URL-ben megvan a `?key=bf4Gde67f`.
2. Üres rendelés (minden 0) küldése → hiba: „Legalább egy terméket ki kell választanod."
3. Házszám 0/üresen hagyva → hiba a házszámról.
4. Mikorra a múltban → hiba az időpontról.
5. Érvényes rendelés (pl. Brownie 2, Limonádé 1, házszám 5, jövőbeli időpont) → átirányít a successfulOrder.php-ra, a `key` megvan az URL-ben, a rendelés részletei csak a `>0` tételeket mutatják.
6. A successfulOrder.php frissítése (F5) → vissza a sendOrder.php-ra (session elfogyott), nem küld újra.
7. Navbar linkek a sendOrder és successfulOrder oldalon mind viszik a `key`-t.

`kill %1` a szerver leállításához.

- [ ] **Step 2: Éles e-mail ellenőrzés**

Éles környezetben (vagy valós SMTP-vel) adj le egy tesztrendelést, opcionális e-mail mezővel:
1. A `zoebaloght@gmail.com` megkapja a levelet, tárgy: `Kávékaland - Megrendelés - <4 karakter>`.
2. A 4 BCC-címzett megkapja (a To/CC fejlécben NEM látszanak).
3. Ha az opcionális e-mail érvényes → CC-ben megjelenik; ha hibás formátumú → a levél a többieknek akkor is megérkezik, CC nélkül.
4. A levél törzse csoportosítva, csak a rendelt tételekkel, szállítási és (ha megadva) megrendelői adatokkal, megjegyzéssel.

- [ ] **Step 3: Nincs commit** (csak verifikáció). Ha bug derül ki, javítás a megfelelő task mintája szerint, majd külön commit.

---

## Self-Review

**Spec coverage:**
- Sütemények/Italok blokkok + max mennyiségek → Task 1 (`ca_menu`) + Task 2 (form render). ✓
- Szállítási adatok (Mikorra +30p/kerekítés, Utca ABC, Házszám 1-100) → Task 2. ✓
- Megrendelő adatai (opcionális) + tájékoztató szöveg → Task 2. ✓
- Megjegyzés textarea ~4 sor → Task 2. ✓
- Legalább 1 tétel → Task 2. ✓
- Böngésző + szerver időpont-validáció → Task 2 (`min` attr + PHP check). ✓
- 4 karakteres véletlen ID → Task 1 (`ca_order_id`). ✓
- E-mail To/BCC/CC szabály, tárgy, formázott törzs → Task 1 (`ca_build_body`) + Task 3. ✓
- Hibás opcionális e-mail nem blokkolja a küldést → Task 3 (`filter_var` csak a CC-re). ✓
- Hiba → marad sendOrder.php-n, értékmegőrzés → Task 2 (POST visszatöltés) + Task 3 (catch → `$errors`). ✓
- successfulOrder.php azonos stílus + köszönőszöveg + részletek → Task 4. ✓
- Session-alapú átadás → Task 3 (mentés) + Task 4 (olvasás/törlés). ✓
- index.php szekció csere + gomb + key → Task 5. ✓
- key továbbvitel mindenhol, kivéve unauthorized → minden task (linkek, redirectek `urlencode`/`htmlspecialchars`). ✓

**Placeholder scan:** Nincs TBD/TODO; minden kódlépés teljes kódot ad. ✓

**Type consistency:** `ca_menu`, `ca_streets`, `ca_order_id`, `ca_collect_quantities`, `ca_total_items`, `ca_build_body` szignatúrái Task 1-ben definiálva, Task 2/3-ban azonos névvel és paraméterekkel használva. `$_SESSION['ca_order']` kulcsai (`id`, `body`) Task 3-ban írva, Task 4-ben olvasva. ✓

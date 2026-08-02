# sendOrder.php Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a `sendOrder.php` page that displays a pre-filled order form and sends its contents via Gmail SMTP to two recipients.

**Architecture:** A single PHP file handles both GET (render form) and POST (send email). PHPMailer is included manually (no Composer). Gmail credentials live in a gitignored `smtp_config.php`.

**Tech Stack:** PHP, PHPMailer 6.x (manual install), Gmail SMTP TLS port 587

## Global Constraints

- Hosting: InfinityFree (no Composer, no shell access)
- Access control: `?key=` param required, valid values `aaBB555` and `CCdd22`, redirect to `unauthorized.php` on failure
- Language: Hungarian UI text
- Style: must use existing `style.css` and Poppins font — no new CSS files
- SMTP credentials must NOT be committed to git

---

### Task 1: PHPMailer fájlok letöltése

**Files:**
- Create: `PHPMailer/PHPMailer.php`
- Create: `PHPMailer/SMTP.php`
- Create: `PHPMailer/Exception.php`

**Interfaces:**
- Produces: `PHPMailer\PHPMailer\PHPMailer`, `PHPMailer\PHPMailer\SMTP`, `PHPMailer\PHPMailer\Exception` classes used by Task 3

- [ ] **Step 1: PHPMailer fájlok letöltése**

Nyisd meg a böngészőben a PHPMailer GitHub repóját és töltsd le a három szükséges fájlt a `src/` mappából:

```
https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php
https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php
https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php
```

Mentsd őket a projekt gyökerében lévő `PHPMailer/` mappába:
```
CoffeeAdvanture/
└── PHPMailer/
    ├── PHPMailer.php
    ├── SMTP.php
    └── Exception.php
```

- [ ] **Step 2: Ellenőrzés**

Nyisd meg a `PHPMailer/PHPMailer.php` fájlt és ellenőrizd, hogy tartalmazza a `class PHPMailer` definíciót (nem üres, nem HTML hibaoldal).

- [ ] **Step 3: Commit**

```bash
git add PHPMailer/PHPMailer.php PHPMailer/SMTP.php PHPMailer/Exception.php
git commit -m "Add PHPMailer library files (manual install)"
```

---

### Task 2: smtp_config.php és .gitignore

**Files:**
- Create: `smtp_config.php`
- Modify: `.gitignore`

**Interfaces:**
- Produces: `SMTP_USER` és `SMTP_PASS` konstansok, amelyeket Task 3 használ

- [ ] **Step 1: smtp_config.php létrehozása**

Hozd létre a `smtp_config.php` fájlt az alábbi tartalommal, és cseréld ki az `APP_PASSWORD_HERE` részt a Google App Passwordre (16 karakter, szóközök nélkül):

```php
<?php
define('SMTP_USER', 'boglarka.boka@gmail.com');
define('SMTP_PASS', 'APP_PASSWORD_HERE');
```

- [ ] **Step 2: .gitignore bővítése**

Ha még nincs `.gitignore` a projektben, hozd létre. Ha már van, add hozzá ezt a sort:

```
smtp_config.php
```

- [ ] **Step 3: Ellenőrzés — smtp_config.php NEM kerül gitbe**

```bash
git status
```

A `smtp_config.php` ne jelenjen meg a `Changes to be committed` vagy `Untracked files` listában.

- [ ] **Step 4: Commit**

```bash
git add .gitignore
git commit -m "Add .gitignore with smtp_config.php excluded"
```

---

### Task 3: sendOrder.php létrehozása

**Files:**
- Create: `sendOrder.php`

**Interfaces:**
- Consumes: `PHPMailer/PHPMailer.php`, `PHPMailer/SMTP.php`, `PHPMailer/Exception.php` (Task 1-ből), `SMTP_USER` és `SMTP_PASS` konstansok (Task 2-ből), `style.css`, `unauthorized.php`
- Produces: működő rendelési oldal `?key=` védelemmel

- [ ] **Step 1: sendOrder.php létrehozása**

Hozd létre a `sendOrder.php` fájlt az alábbi teljes tartalommal:

```php
<?php
$allowed = ['aaBB555', 'CCdd22'];
if (!isset($_GET['key']) || !in_array($_GET['key'], $allowed, true)) {
    header('Location: unauthorized.php');
    exit;
}

$key = htmlspecialchars($_GET['key']);
$success = false;
$error = '';
$defaultTemplate = "Termék: \nMennyiség: \nNév: \nIdőpont: \nLakcím: \nMegjegyzés: ";
$orderText = $defaultTemplate;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderText = isset($_POST['order']) ? trim($_POST['order']) : '';

    if ($orderText === '') {
        $error = 'A rendelés szövege nem lehet üres.';
    } else {
        require_once 'smtp_config.php';
        require_once 'PHPMailer/Exception.php';
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        use PHPMailer\PHPMailer\Exception;

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
            $mail->addAddress('gabor.boka.jr@gmail.com');
            $mail->addAddress('boglarka.boka@gmail.com');

            $mail->Subject = 'Rendelés – ' . date('Y-m-d H:i');
            $mail->Body    = $orderText;

            $mail->send();
            $success = true;
            $orderText = $defaultTemplate;
        } catch (Exception $e) {
            $error = 'Hiba az e-mail küldésekor: ' . $mail->ErrorInfo;
        }
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
        }
        .order-box textarea {
            width: 100%;
            min-height: 200px;
            padding: 16px;
            border-radius: 20px;
            border: none;
            font-family: "Poppins", sans-serif;
            font-size: 16px;
            color: #1d2652;
            resize: vertical;
            margin: 20px 0;
        }
        .order-box textarea:focus {
            outline: 3px solid #FFEDA8;
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
        .msg-success {
            margin-top: 18px;
            color: #a8ffb0;
            font-weight: 700;
            font-size: 17px;
        }
        .msg-error {
            margin-top: 18px;
            color: #ffaaaa;
            font-weight: 700;
            font-size: 17px;
        }
    </style>
</head>
<body>

    <header>
        <nav class="navbar">
            <div class="logo">KávéKaland</div>
            <ul>
                <li><a href="index.php?key=<?= $key ?>">Kezdőlap</a></li>
                <li><a href="index.php?key=<?= $key ?>#products">Termékek</a></li>
                <li><a href="index.php?key=<?= $key ?>#team">Csapat</a></li>
                <li><a href="index.php?key=<?= $key ?>#order">Rendelés info</a></li>
                <li><a href="index.php?key=<?= $key ?>#contact">Kapcsolat</a></li>
            </ul>
        </nav>
    </header>

    <section class="order send-order-section">
        <div class="order-box">
            <h3>Rendelés leadása</h3>
            <p>Töltsd ki az alábbi mezőt és küldd el rendelésedet!</p>
            <form method="POST" action="sendOrder.php?key=<?= $key ?>">
                <textarea name="order"><?= htmlspecialchars($orderText) ?></textarea>
                <br>
                <button type="submit" class="send-btn">Rendelés elküldése</button>
            </form>
            <?php if ($success): ?>
                <p class="msg-success">Rendelésedet sikeresen elküldtük! Hamarosan felvesszük veled a kapcsolatot.</p>
            <?php elseif ($error !== ''): ?>
                <p class="msg-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
        </div>
    </section>

</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add sendOrder.php
git commit -m "Add sendOrder.php with Gmail SMTP form"
```

---

### Task 4: Deployment és élő teszt

**Files:**
- Nincs fájlmódosítás — ez egy manuális ellenőrzési lépés

- [ ] **Step 1: FTP-re feltöltés ellenőrzése**

Pushold a `main` branchet GitHubra — a GitHub Actions automatikusan feltölti FTP-n keresztül:

```bash
git push origin main
```

Ellenőrizd a GitHub Actions logot, hogy a deploy sikeres volt-e.

- [ ] **Step 2: smtp_config.php manuális feltöltése**

Az `smtp_config.php` nincs a repóban (gitignore), ezért **kézzel kell feltölteni** FTP-vel az InfinityFree `htdocs/` mappájába. Használj FileZilla-t vagy az InfinityFree fájlkezelőjét.

- [ ] **Step 3: Élő teszt**

Nyisd meg böngészőben:
```
https://coffeeadventure.rf.gd/sendOrder.php?key=aaBB555
```

Ellenőrzések:
- Megjelenik az oldal a navbar-ral és a kitöltött textarea-val
- Érvénytelen kulccsal (`?key=rossz`) átirányít az unauthorized.php-ra
- Rendelés elküldése után megjelenik a zöld sikeres üzenet
- A két e-mail cím megkapja a levelet

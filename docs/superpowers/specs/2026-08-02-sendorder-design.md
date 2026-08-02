# sendOrder.php – Design Spec

**Dátum:** 2026-08-02  
**Projekt:** KávéKaland (CoffeeAdventure)

---

## Cél

Egy rendelési űrlap oldal, amely e-mailt küld Gmail SMTP-n keresztül. Az oldal stílusa megegyezik az `index.php`-éval.

---

## Fájlstruktúra

```
CoffeeAdvanture/
├── sendOrder.php          # rendelési oldal
├── smtp_config.php        # Gmail SMTP adatok (gitignore-ban)
├── PHPMailer/
│   ├── PHPMailer.php
│   ├── SMTP.php
│   └── Exception.php
└── .gitignore             # smtp_config.php bejegyzéssel bővítve
```

## Hozzáférés-ellenőrzés

Ugyanaz a `?key=` mechanizmus mint az `index.php`-ban. Érvénytelen kulcsnál átirányítás `unauthorized.php`-ra.

---

## Megjelenés

- Ugyanaz a `style.css` és Poppins font
- Ugyanaz a navbar az `index.php` szekcióira mutató linkekkel
- Középre igazított `order-box` stílusú kártya, benne:
  - Cím: „Rendelés leadása"
  - `<textarea>` előre kitöltve rendelési sablonnal
  - „Rendelés elküldése" gomb (`main-btn` stílusban)
  - Sikeres küldés után: zöld visszajelzés szöveg a gomb alatt, textarea visszaáll a sablonra
  - Hiba esetén: piros hibaüzenet

---

## Rendelési sablon (textarea alapértéke)

```
Termék: 
Mennyiség: 
Név: 
Időpont: 
Lakcím: 
Megjegyzés: 
```

---

## E-mail küldés logikája

**Könyvtár:** PHPMailer (kézi feltöltés, Composer nélkül)  
**SMTP:** `smtp.gmail.com`, port `587`, TLS  
**Feladó:** `boglarka.boka@gmail.com`  
**Címzettek:** `gabor.boka.jr@gmail.com`, `boglarka.boka@gmail.com`  
**Subject:** `Rendelés – YYYY-MM-DD HH:MM` (aktuális dátum+idő)  
**Body:** a textarea tartalma (plain text)

---

## smtp_config.php tartalma

```php
<?php
define('SMTP_USER', 'boglarka.boka@gmail.com');
define('SMTP_PASS', 'app-password-here');
```

Ez a fájl **nem kerül fel GitHubra** (`.gitignore` bejegyzés).

---

## Feldolgozási folyamat

1. `?key=` ellenőrzés – érvénytelen kulcsnál redirect
2. POST kérés esetén: textarea tartalom sanitizálása
3. `smtp_config.php` betöltése
4. PHPMailer konfiguráció és küldés
5. Sikeres küldés: `$success = true` → zöld üzenet, textarea visszaáll
6. Hiba: `$error = $mail->ErrorInfo` → piros hibaüzenet

---

## Biztonság

- `smtp_config.php` `.gitignore`-ban
- Textarea tartalom `htmlspecialchars()` sanitizálással kerül az e-mailbe

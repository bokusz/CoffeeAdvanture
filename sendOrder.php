<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
date_default_timezone_set('Europe/Budapest');

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

// A "Mikorra" minimum értéke: most + 30 perc.
$minTs  = time() + 30 * 60;

// A "Mikorra" mező formátuma: yyyy.MM.dd HH:mm (magyar formátum).
$whenFormat = 'Y.m.d H:i';

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
$defaultWhen = date($whenFormat, $roundedTs);

// Form értékek (POST visszatöltéshez).
$qty = [];
foreach ($menu as $items) {
    foreach ($items as $pkey => $meta) {
        $qty[$pkey] = 0;
    }
}
$when     = $defaultWhen;
$street   = 'Malomvölgy utca';
$houseNo  = '';
$email    = '';
$phone    = '';
$note     = '';

$readyToSend = false;
$orderId = '';
$whenError = '';

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

    // Mikorra: kötelező, yyyy.MM.dd HH:mm formátum, és >= most + 30 perc.
    $whenDt = DateTime::createFromFormat('!' . $whenFormat, $when);
    $whenValid = $whenDt !== false && $whenDt->format($whenFormat) === $when;
    if ($when === '' || !$whenValid) {
        $whenError = 'Hibás időpont. Kérjük yyyy.MM.dd HH:mm formátumban add meg (pl. ' . $defaultWhen . ').';
    } elseif ($whenDt->getTimestamp() < $minTs) {
        $whenError = 'A szállítási időpont legalább 30 perccel a mostani időpont után legyen (pl. ' . $defaultWhen . ').';
    } else {
        $whenDate = (int) $whenDt->format('Ymd');
        $whenHi   = (int) $whenDt->format('Hi');
        if ($whenDate < 20260804 || $whenDate > 20260807) {
            $whenError = 'A rendelést csak 2026.08.04 és 2026.08.07 közötti napokra lehet feladni.';
        } elseif ($whenHi < 1300 || $whenHi > 1800) {
            $whenError = 'A szállítási időpont csak 13:00 és 18:00 között lehet.';
        }
    }
    if ($whenError !== '') {
        $errors[] = $whenError;
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

            $subject = 'KávéKaland - Megrendelés - ' . $orderId;
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = ca_build_html_body($body, $subject, $key);
            $mail->AltBody = $body;

            $mail->send();

            $_SESSION['ca_order'] = ['id' => $orderId, 'body' => $body];
            header('Location: successfulOrder.php?key=' . urlencode($key));
            exit;
        } catch (Exception $e) {
            $errors[] = 'Hiba az e-mail küldésekor: ' . $mail->ErrorInfo;
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
            padding: 40px 20px;
        }
        .order-form-wrap {
            width: 100%;
            max-width: 640px;
        }
        .order-block {
            background: #154061;
            border-radius: 20px;
            padding: 22px 26px;
            margin: 18px 0;
            box-shadow: 0 30px 70px rgba(21,64,97,.3);
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
            background: linear-gradient(135deg, #D6E4F5 0%, #A7C1E1 100%);
            color: #154061;
            font-family: "Poppins", sans-serif;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: .3s;
            box-shadow: 0 10px 25px rgba(167,193,225,.5);
        }
        .send-btn:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 20px 35px rgba(167,193,225,.6);
        }
        .msg-error {
            margin: 0 0 18px;
            padding: 14px 18px;
            background: rgba(255,120,120,.55);
            border-radius: 14px;
            color: #392222;
            font-weight: 600;
        }
        .msg-error ul { margin: 8px 0 0; padding-left: 20px; }
        .field-hint {
            margin: 4px 0 0;
            color: #FFEDA8;
            font-size: 13px;
        }
        .field-warning {
            margin: 8px 0 0;
            padding: 10px 14px;
            background: rgba(255,120,120,.55);
            border-radius: 12px;
            color: #392222;
            font-size: 14px;
            font-weight: 600;
        }
        .order-row input.input-error {
            border: 2px solid #e05555;
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
                        <input type="text" name="when" id="when"
                               placeholder="yyyy.MM.dd HH:mm"
                               pattern="\d{4}\.\d{2}\.\d{2} \d{2}:\d{2}"
                               title="Formátum: yyyy.MM.dd HH:mm (pl. <?= htmlspecialchars($defaultWhen) ?>)"
                               value="<?= htmlspecialchars($when) ?>"
                               class="<?= $whenError !== '' ? 'input-error' : '' ?>" required>
                    </div>
                    <?php if ($whenError !== ''): ?>
                        <p class="field-warning"><?= htmlspecialchars($whenError) ?></p>
                    <?php endif; ?>
                    <p class="field-hint">Formátum: például <?= htmlspecialchars($defaultWhen) ?><br>Lehetséges szállítási idők: 2026.08.04 - 2026.08.07. 13:00-18:00.</p>
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

                <div class="order-block">
                    <h4>Fizetés</h4>
                    <p class="hint">Kávézónk bizalomra épülő rendszerben működik. Nálunk nincs
                        kötelező ár: annyit fizethetsz, amennyit szerinted megérnek
                        a sütik és italok, illetve amennyivel szeretnéd támogatni a
                        kis kávézónkat. Minden hozzájárulás sokat jelent számunkra,
                        hiszen ebből tudunk új alapanyagokat beszerezni és tovább
                        fejleszteni a kávézót.</p>
                </div>                

                <button type="submit" class="send-btn">Rendelés küldése</button>
            </form>
        </div>
    </section>

</body>
</html>

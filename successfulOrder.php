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

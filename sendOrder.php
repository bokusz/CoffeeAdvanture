<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$allowed=['bf4Gde67f','ndskFCgfj23', 'mjlzbtk3Den', 'df56hdfFV3', 'nzkj57nf2h'];
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

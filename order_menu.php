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

// A visszaigazoló szöveg — ugyanaz, mint a successfulOrder.php oldalon.
function ca_success_message(): string {
    return 'Rendelésedet köszönjük! Feldolgozását megkezdtük. Ha kérdésünk felmerül, keressük, egyéb esteben a megadott időre szállítunk. Bármilyen kérédése felmerül a zoebaloght@gmail.com e-mail címen elér minket.';
}

// HTML e-mail törzs, amely a successfulOrder.php oldal tartalmát és stílusát
// tükrözi (menü nélkül). A $body a ca_build_body() sima szöveges kimenete,
// a $subject a levél tárgya (fejlécként), a $key a megrendelő hozzáférési
// kulcsa (a záró gomb index.php-re mutató linkjéhez).
function ca_build_html_body(string $body, string $subject, string $key): string {
    $title    = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $msg      = htmlspecialchars(ca_success_message(), ENT_QUOTES, 'UTF-8');
    $details  = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
    $indexUrl = 'https://coffeeadventure.rf.gd/index.php?key=' . rawurlencode($key);
    $indexUrl = htmlspecialchars($indexUrl, ENT_QUOTES, 'UTF-8');

    $block   = 'background:#154061;border-radius:20px;padding:22px 26px;'
             . 'box-shadow:0 30px 70px rgba(21,64,97,.3);color:#ffffff;';

    $html  = '<!DOCTYPE html><html lang="hu"><head><meta charset="UTF-8">'
           . '<meta name="viewport" content="width=device-width, initial-scale=1.0"></head>';
    $html .= '<body style="margin:0;padding:0;background:#FFF7D6;'
           . 'font-family:\'Poppins\',Arial,sans-serif;color:#1d2652;">';
    $html .= '<div style="max-width:640px;margin:0 auto;padding:40px 20px;">';
    $html .= '<h1 style="margin:0 0 28px;text-align:center;color:#154061;'
           . 'font-size:26px;font-weight:900;letter-spacing:-1px;">' . $title . '</h1>';
    $html .= '<p style="' . $block . 'font-size:19px;font-weight:600;line-height:1.5;'
           . 'margin:0 0 28px;">' . $msg . '</p>';
    $html .= '<div style="' . $block . 'text-align:left;white-space:pre-wrap;'
           . 'font-size:15px;line-height:1.5;">'
           . '<h4 style="margin:0 0 14px;color:#FFEDA8;font-size:18px;">Rendelés részletei</h4>'
           . $details . '</div>';
    $html .= '<div style="text-align:center;margin:32px 0 0;">'
           . '<a href="' . $indexUrl . '" '
           . 'style="display:inline-block;padding:16px 35px;border-radius:50px;'
           . 'background:#A7C1E1;'
           . 'background:linear-gradient(135deg,#D6E4F5 0%,#A7C1E1 100%);color:#154061;'
           . 'font-family:\'Poppins\',Arial,sans-serif;font-size:16px;font-weight:800;'
           . 'text-decoration:none;box-shadow:0 10px 25px rgba(167,193,225,.5);">'
           . 'KávéKaland</a></div>';
    $html .= '</div></body></html>';

    return $html;
}

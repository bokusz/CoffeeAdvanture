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

<?php
$allowed=['bf4Gde67f','ndskFCgfj23', 'mjlzbtk3Den', 'df56hdfFV3', 'nzkj57nf2h'];
if(!isset($_GET['key'])||!in_array($_GET['key'],$allowed,true)){header('Location: unauthorized.php');exit;}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KávéKaland | Kávé, süti és élmények</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>
<body>

    <!-- Az oldal példa meghívása:
        https://coffeeadventure.rf.gd/index.php?key=bf4Gde67f
        https://coffeeadventure.rf.gd/index.php?key=ndskFCgfj23
        https://coffeeadventure.rf.gd/index.php?key=mjlzbtk3Den
        https://coffeeadventure.rf.gd/index.php?key=df56hdfFV3
        https://coffeeadventure.rf.gd/index.php?key=nzkj57nf2h
    -->

    <!-- ==========================
        NAVIGÁCIÓ
    ========================== -->
    <header>
        <nav class="navbar">
            <div class="logo">
                KávéKaland
            </div>
            <ul>
                <li><a href="#home">Kezdőlap</a></li>
                <li><a href="#products">Termékek</a></li>
                <li><a href="#team">Csapat</a></li>
                <li><a href="#order">Rendelés</a></li>
                <li><a href="#contact">Kapcsolat</a></li>
            </ul>
        </nav>
    </header>

    <!-- ==========================
        HERO
    ========================== -->
    <section class="hero" id="home">
        <div class="hero-text">
            <div class="badge">
                Kézműves sütik & italok (Bóki, Bogi)
            </div>
            <h1>
                Kávé.
                <br>
                Süti.
                <br>
                <span>Élmények.</span>
            </h1>
            <p>
                Frissen készített finomságok,
                házi sütemények és különleges italok
                egy helyen.
            </p>
            <div class="buttons">
                <a href="#products" class="main-btn">
                    Megnézem a kínálatot
                </a>
                <a href="#order" class="second-btn">
                    Rendelés
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="images/lemonade.jpg" alt="Limonádé">
        </div>
    </section>

    <!-- ==========================
        TERMÉKEINK
    ========================== -->
    <section class="products" id="products">
        <h2>
            Termékeink
        </h2>
        <p class="section-text">
            Válaszd ki kedvencedet a frissen készített finomságaink közül.
        </p>
        <div class="product-container">
            <div class="product-card" data-product="lemonade">
                <img src="images/lemonade.jpg">
                <h3>
                    Limonádé
                </h3>
                <p>
                    Frissítő házi limonádé többféle ízben.
                </p>
            </div>
            <div class="product-card" data-product="brownie">
                <img src="images/brownie.jpg">
                <h3>
                    Brownie
                </h3>
                <p>
                    Puha, gazdag csokoládés sütemény.
                </p>
            </div>
            <div class="product-card" data-product="cookie">
                <img src="images/cookiesprob.png">
                <h3>
                    Chocolate Chip Cookie
                </h3>
                <p>
                    Amerikai stílusú cookie sok csokoládéval.
                </p>
            </div>
            <div class="product-card" data-product="poffeteg">
                <img src="images/csokispoffeteg.jpg">
                <h3>
                    Csokis pöffeteg
                </h3>
                <p>
                    Omlós csokoládés finomság porcukorral.
                </p>
            </div>
            <div class="product-card" data-product="coffee">
                <img src="images/jegeskave.jpg">
                <h3>
                    Jegeskávé
                </h3>
                <p>
                    Frissen készített jegeskávé vaníliafagyival és házi karamellszósszal
                </p>
            </div>
            <div class="product-card" data-product="coffee">
                <img src="images/tejeskave.jpg">
                <h3>
                    Tejeskávé
                </h3>
                <p>
                    Habos tegeskávé selymes lágy tejjel és gazdag espresso ízzel.
                </p>
            </div>
        </div>
    </section>

    <!-- ==========================
        CSAPAT
    ========================== -->
    <section class="team" id="team">
        <h2>
            A csapatunk
        </h2>
        <div class="team-grid">
            <div class="team-card">
                <img src="images/bogi.jpg">
                <h3>
                    Boglárka
                </h3>
                <span>
                    Alapító
                </span>
                <p>
                    Sziasztok! Bogi vagyok 14 éves, én álmodtam meg ezt a kis
                    kávézót. Imádok sütni, ezért én készítem a sütiket, emellett
                    én készítettem a plakátokat és ezt a weboldalt is. Nagyon örülök,
                    hogy megvalósulhatott ez az ötletem, és remélem, hogy mindenki
                    jól érzi majd magát nálunk!
                </p>
            </div>
            <div class="team-card">
                <img src="images/bibi.jpg">
                <h3>
                    BÍborka
                </h3>
                <span>
                    Segítő
                </span>
                <p>
                    Sziasztok! Bibi vagyok Bogi testvére és 11 éves. Szeretek segíteni Boginak a
                    kávézóban, ezért gyakran szoktuk együtt sütni, és én
                    segítek a sütik és italok felszolgálásában is. Nagyon örülök,
                    hogy részese lehetek ennek a kis kávézónak, és remélem, hogy
                    minden vendégnek ízlei fognak a finomságaink
                </p>
            </div>
            <div class="team-card">
                <img src="images/nati.png">
                <h3>
                    Natália
                </h3>
                <span>
                    Kreatív tervező
                </span>
                <p>
                    Sziasztok! Nati vagyok, 11 éves, Nagyon szeretek kreatívkodni, ezért én is
                    készítettem plakátokat és süti illusztrációkat is a kávézóhoz. Emellett
                    gyakran segítek Boginak a sütésben, és örömmel veszek részt a
                    sütik és italok árusításában is. Boldoggá tesz, hogy én is
                    hozzájárulhatok ehhez a kis kávézóhoz
                </p>
            </div>
        </div>
    </section>

    <!-- ==========================
        RENDELÉS
    ========================== -->
    <section class="order" id="order">
        <h2>
            Hogyan rendelj?
        </h2>
        <div class="order-box">
            <p>A megrendelését a következő űrlapon tudja megtenni:</p>
            <div class="buttons">
                <a class="second-btn" href="sendOrder.php?key=<?= htmlspecialchars($_GET['key']) ?>">Rendelés feladása</a>
            </div>
        </div>
    </section>

    <!-- ==========================
        KAPCSOLAT
    ========================== -->
    <section class="contact" id="contact">
        <h2>
            Kapcsolat
        </h2>
        <div class="contact-box">
            <p>
                📍 Malomvölgy utca 36.
            </p>
            <p>
                📧 zoebaloght@gmail.com
            </p>
        </div>
    </section>
    <script src="script.js"></script>

    <!-- ==========================
        VÉLEMÉNYEK
    ========================== -->
    <section class="reviews" id="reviews">
        <h2>
            Vásárlóink véleménye
        </h2>
        <div class="review-container">
            <div class="review-card">
                <div class="stars">
                    ★★★★★
                </div>
                <p>
                    "A brownie egyszerűen tökéletes volt,
                    a limonádé pedig nagyon frissítő."
                </p>
                <h3>
                    Jutka
                </h3>
            </div>
            <div class="review-card">
                <div class="stars">
                    ★★★★★
                </div>
                <p>
                    "Nagyon finom sütik és kedves kiszolgálás.
                    Biztosan visszatérünk!"
                </p>
                <h3>
                    Tibor
                </h3>
            </div>
            <div class="review-card">
                <div class="stars">
                    ★★★★★
                </div>
                <p>
                    "A cookie olyan, mint egy igazi amerikai süti.
                    Nagyon ajánlom!"
                </p>
                <h3>
                    Lili
                </h3>
            </div>
        </div>
    </section>
    </section>
</body>
</html>
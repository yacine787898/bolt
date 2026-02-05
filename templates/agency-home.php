<?php
/** @var array<string, string>|null $flash */
/** @var array<int, array<string, string>> $services */
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meneeto Agency - Communication & Publicité en ligne</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWix+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkR4j8R2R52nqnVvP4YfR9Wqj8K8xkP4Vxg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --gradient: linear-gradient(120deg, #fddeff, #bb58ff, #037bff);
            --text: #1f2235;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #fff;
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: -25%;
            background: radial-gradient(circle at 15% 20%, rgba(187, 88, 255, 0.24), transparent 36%),
                        radial-gradient(circle at 85% 12%, rgba(3, 123, 255, 0.2), transparent 38%),
                        radial-gradient(circle at 50% 80%, rgba(253, 222, 255, 0.4), transparent 45%);
            z-index: -1;
            animation: bgMove 10s ease-in-out infinite alternate;
        }
        @keyframes bgMove { from { transform: translateY(0); } to { transform: translateY(-30px); } }

        header {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 4vw;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.76);
            border-bottom: 1px solid rgba(255,255,255,0.8);
        }
        .logo img { height: 54px; width: auto; display: block; }
        nav { display: flex; gap: 16px; flex-wrap: wrap; }
        nav a {
            text-decoration: none;
            color: #2c2f4a;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.4);
            transition: .25s ease;
        }
        nav a:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(32, 53, 117, 0.15); }

        section { max-width: 1100px; margin: 36px auto; padding: 0 20px; }
        .glass {
            background: rgba(255,255,255,0.46);
            border: 1px solid rgba(255,255,255,0.65);
            box-shadow: 0 18px 40px rgba(22, 38, 90, 0.13);
            border-radius: 24px;
            backdrop-filter: blur(12px);
        }

        .hero {
            min-height: 68vh;
            background: linear-gradient(rgba(10,10,30,.56), rgba(10,10,30,.52)),
                        url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
            color: white;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 40px;
        }
        .hero h1 { font-size: clamp(2rem, 6vw, 3.8rem); margin: 0; }
        .hero p { max-width: 760px; font-size: clamp(1rem, 2.6vw, 1.25rem); opacity: .95; }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            border-radius: 999px;
            text-decoration: none;
            color: white;
            font-weight: 600;
            background: var(--gradient);
            box-shadow: inset 0 2px 2px rgba(255,255,255,.5), 0 10px 24px rgba(64, 36, 142, 0.35);
            transition: .2s;
        }
        .btn:hover { transform: translateY(-2px) scale(1.02); }

        .section-title { margin: 0 0 18px; font-size: 2rem; }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }
        .service-card {
            padding: 22px;
            text-decoration: none;
            color: inherit;
            transition: .2s ease;
            display: block;
        }
        .service-card:hover { transform: translateY(-5px); }
        .service-card.disabled { opacity: .75; cursor: not-allowed; }
        .service-icon {
            width: 50px; height: 50px;
            border-radius: 14px;
            display: grid; place-items: center;
            background: var(--gradient);
            color: white;
            box-shadow: inset 0 1px 2px rgba(255,255,255,.4);
            margin-bottom: 14px;
        }

        .contact-wrap, .about-wrap { padding: 26px; }
        form { display: grid; gap: 12px; }
        input, textarea {
            border: 1px solid rgba(255,255,255,0.65);
            background: rgba(255,255,255,0.65);
            border-radius: 12px;
            padding: 12px;
            font: inherit;
        }
        textarea { min-height: 170px; resize: vertical; }

        .flash { padding: 11px 12px; border-radius: 10px; margin-bottom: 10px; }
        .flash.success { background: rgba(43, 172, 100, 0.2); }
        .flash.error { background: rgba(234, 81, 81, 0.2); }

        .socials { display: flex; flex-wrap: wrap; gap: 12px; }
        .socials a {
            width: 44px; height: 44px;
            border-radius: 50%;
            display: inline-grid; place-items: center;
            color: white;
            background: var(--gradient);
            text-decoration: none;
        }
        footer { text-align: center; padding: 28px 20px 40px; color: #4f4f66; }
    </style>
</head>
<body>
<header>
    <a class="logo" href="#home"><img src="/uploads/logo.png" alt="Logo Meneeto"></a>
    <nav>
        <a href="#home">Accueil</a>
        <a href="#services">Services</a>
        <a href="#contact">Contact</a>
        <a href="#about">À propos</a>
    </nav>
</header>

<section id="home" class="hero glass">
    <div>
        <h1>Boostez votre visibilité digitale</h1>
        <p>Agence de communication et publicité en ligne : nous créons des expériences web modernes, des solutions IA et des outils marketing pensés pour faire grandir votre business.</p>
        <a class="btn" href="#services">Découvrir nos services</a>
    </div>
</section>

<section id="services">
    <h2 class="section-title">Nos services</h2>
    <div class="services-grid">
        <?php foreach ($services as $service): ?>
            <?php
            $isExternal = str_starts_with($service['url'], 'http');
            $isDisabled = $service['url'] === '';
            ?>
            <a
                class="service-card glass <?= $isDisabled ? 'disabled' : '' ?>"
                <?= $isDisabled ? 'aria-disabled="true"' : 'href="' . htmlspecialchars($service['url']) . '"' ?>
                <?= $isExternal ? 'target="_blank" rel="noopener"' : '' ?>
            >
                <div class="service-icon"><i class="fa-solid <?= htmlspecialchars($service['icon']) ?>"></i></div>
                <h3><?= htmlspecialchars($service['title']) ?></h3>
                <p><?= htmlspecialchars($service['description']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section id="contact">
    <div class="contact-wrap glass">
        <h2 class="section-title">Contact</h2>
        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['text']) ?></div>
        <?php endif; ?>
        <form method="post" action="/contact">
            <input type="text" name="name" placeholder="Nom" required>
            <input type="email" name="email" placeholder="Adresse email" required>
            <textarea name="message" maxlength="1000" placeholder="Votre message (1000 caractères max)" required></textarea>
            <button type="submit" class="btn" style="border:none; cursor:pointer;">Envoyer</button>
        </form>
    </div>
</section>

<section id="about">
    <div class="about-wrap glass">
        <h2 class="section-title">À propos</h2>
        <p>Nous aidons les marques à se démarquer grâce à des solutions web, créatives et pilotées par la performance.</p>
        <div class="socials">
            <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="mailto:contact@meneeto.com" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
            <a href="https://wa.me/213660890203" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        </div>
    </div>
</section>

<footer>© <?= date('Y') ?> Meneeto Agency. Tous droits réservés.</footer>
</body>
</html>

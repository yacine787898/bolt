<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matcha Atelier - Boutique en ligne</title>
    <style>
        :root {
            --matcha-900: #1f3a2d;
            --matcha-700: #2f5d46;
            --matcha-500: #4c8c63;
            --matcha-200: #dff3e5;
            --cream: #f7f4ee;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: var(--cream);
            color: var(--matcha-900);
        }
        header {
            padding: 1.5rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a { color: var(--matcha-700); text-decoration: none; font-weight: 600; }
        .hero {
            margin: 0 2.5rem;
            background: linear-gradient(120deg, rgba(47, 93, 70, 0.92), rgba(223, 243, 229, 0.9));
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            display: grid;
            gap: 1rem;
        }
        .hero h1 { font-size: 2.2rem; margin: 0; }
        .hero p { max-width: 520px; }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            padding: 2rem 2.5rem 4rem;
        }
        .card {
            background: white;
            border-radius: 18px;
            padding: 1rem;
            box-shadow: 0 12px 30px rgba(31, 58, 45, 0.1);
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }
        .card img { width: 100%; border-radius: 14px; height: 180px; object-fit: cover; }
        .price { font-weight: 700; color: var(--matcha-700); }
        .actions { margin-top: auto; display: flex; gap: 0.5rem; }
        .button {
            padding: 0.6rem 1rem;
            border-radius: 999px;
            border: none;
            background: var(--matcha-700);
            color: white;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .button.secondary {
            background: #e8eee9;
            color: var(--matcha-900);
        }
        .cart-badge { background: var(--matcha-700); color: white; padding: 0.2rem 0.6rem; border-radius: 999px; margin-left: 0.4rem; }
    </style>
</head>
<body>
    <header>
        <strong>Matcha Atelier</strong>
        <div>
            <a href="/cart">Panier <span class="cart-badge"><?= htmlspecialchars((string) $cartCount) ?></span></a>
        </div>
    </header>

    <section class="hero">
        <h1>La boutique matcha qui apaise vos matinées.</h1>
        <p>Découvrez nos poudres premium, nos lattes prêts à savourer et nos accessoires pour un rituel matcha parfait.</p>
        <div class="actions">
            <a class="button" href="#catalogue">Découvrir le catalogue</a>
            <a class="button secondary" href="/admin/login">Espace admin</a>
        </div>
    </section>

    <section id="catalogue" class="grid">
        <?php if (empty($products)) : ?>
            <p>Aucun produit disponible.</p>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <div class="card">
                    <?php if (!empty($product['image_url'])) : ?>
                        <img src="<?= htmlspecialchars((string) $product['image_url']) ?>" alt="<?= htmlspecialchars((string) $product['name']) ?>">
                    <?php endif; ?>
                    <h3><?= htmlspecialchars((string) $product['name']) ?></h3>
                    <p><?= htmlspecialchars((string) $product['description']) ?></p>
                    <div class="price"><?= htmlspecialchars(number_format((float) $product['price'], 0, ',', ' ')) ?> DA</div>
                    <div class="actions">
                        <a class="button secondary" href="/produit/<?= htmlspecialchars((string) $product['slug']) ?>">Voir la page</a>
                        <form method="post" action="/cart/add">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars((string) $product['id']) ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button class="button" type="submit">Ajouter</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $product['name']) ?> - Matcha Atelier</title>
    <style>
        :root {
            --matcha-900: #1f3a2d;
            --matcha-700: #2f5d46;
            --matcha-200: #dff3e5;
            --cream: #f7f4ee;
        }
        body { margin: 0; font-family: "Segoe UI", sans-serif; background: var(--cream); color: var(--matcha-900); }
        header { padding: 1.5rem 2.5rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: var(--matcha-700); text-decoration: none; font-weight: 600; }
        .container { padding: 0 2.5rem 4rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; }
        .card { background: white; border-radius: 22px; padding: 1.5rem; box-shadow: 0 12px 30px rgba(31, 58, 45, 0.1); }
        img { width: 100%; border-radius: 16px; height: 320px; object-fit: cover; }
        .price { font-size: 1.5rem; font-weight: 700; color: var(--matcha-700); }
        button { padding: 0.7rem 1.2rem; border-radius: 999px; border: none; background: var(--matcha-700); color: white; font-weight: 600; cursor: pointer; }
        .badge { background: var(--matcha-200); color: var(--matcha-700); padding: 0.2rem 0.7rem; border-radius: 999px; display: inline-block; }
        .cart-badge { background: var(--matcha-700); color: white; padding: 0.2rem 0.6rem; border-radius: 999px; margin-left: 0.4rem; }
    </style>
</head>
<body>
    <header>
        <a href="/">← Retour à la boutique</a>
        <a href="/cart">Panier <span class="cart-badge"><?= htmlspecialchars((string) $cartCount) ?></span></a>
    </header>

    <section class="container">
        <div class="card">
            <?php if (!empty($product['image_url'])) : ?>
                <img src="<?= htmlspecialchars((string) $product['image_url']) ?>" alt="<?= htmlspecialchars((string) $product['name']) ?>">
            <?php endif; ?>
        </div>
        <div class="card">
            <span class="badge">Matcha premium</span>
            <h1><?= htmlspecialchars((string) $product['name']) ?></h1>
            <p><?= htmlspecialchars((string) $product['description']) ?></p>
            <div class="price"><?= htmlspecialchars(number_format((float) $product['price'], 0, ',', ' ')) ?> DA</div>
            <form method="post" action="/cart/add" style="margin-top: 1.2rem;">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars((string) $product['id']) ?>">
                <label>Quantité</label>
                <input type="number" name="quantity" value="1" min="1" style="width: 80px; margin: 0 0 1rem 0; padding: 0.4rem; border-radius: 10px; border: 1px solid #d9dfd9;">
                <button type="submit">Ajouter au panier</button>
            </form>
        </div>
    </section>
</body>
</html>

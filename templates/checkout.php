<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande - Matcha Atelier</title>
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
        .card { background: white; border-radius: 18px; padding: 1.5rem; box-shadow: 0 12px 30px rgba(31, 58, 45, 0.1); }
        label { display: block; margin-top: 0.9rem; font-weight: 600; }
        input, select { width: 100%; padding: 0.6rem; border-radius: 10px; border: 1px solid #d9dfd9; background: #f9faf8; }
        button { margin-top: 1.2rem; padding: 0.8rem 1.2rem; border-radius: 999px; border: none; background: var(--matcha-700); color: white; font-weight: 600; cursor: pointer; }
        .summary-line { display: flex; justify-content: space-between; margin-bottom: 0.4rem; }
        .muted { color: #6b7d72; font-size: 0.9rem; }
    </style>
</head>
<body>
    <header>
        <a href="/cart">← Retour au panier</a>
        <a href="/">Boutique</a>
    </header>
    <div class="container">
        <form class="card" method="post" action="/checkout">
            <h2>Informations client</h2>
            <label>Nom & prénom</label>
            <input type="text" name="customer_name" required>
            <label>Numéro de téléphone</label>
            <input type="tel" name="customer_phone" required>
            <label>Adresse</label>
            <input type="text" name="customer_address" required>
            <label>Commune (optionnel)</label>
            <input type="text" name="customer_commune">
            <label>Wilaya</label>
            <select name="wilaya_id" required>
                <option value="">Sélectionnez votre wilaya</option>
                <?php foreach ($wilayas as $wilaya) : ?>
                    <option value="<?= htmlspecialchars((string) $wilaya['id']) ?>"><?= htmlspecialchars((string) $wilaya['name']) ?> (Domicile: <?= htmlspecialchars((string) $wilaya['domicile_price']) ?> DA / Stopdesk: <?= htmlspecialchars((string) $wilaya['stopdesk_price']) ?> DA)</option>
                <?php endforeach; ?>
            </select>
            <label>Type de livraison</label>
            <select name="delivery_type" required>
                <option value="domicile">Domicile</option>
                <option value="stopdesk">Bureau (Stopdesk)</option>
            </select>
            <button type="submit">Valider la commande</button>
        </form>
        <div class="card">
            <h2>Récapitulatif</h2>
            <?php foreach ($items as $item) : ?>
                <div class="summary-line">
                    <span><?= htmlspecialchars((string) $item['product']['name']) ?> x<?= htmlspecialchars((string) $item['quantity']) ?></span>
                    <span><?= htmlspecialchars(number_format((float) $item['line_total'], 0, ',', ' ')) ?> DA</span>
                </div>
            <?php endforeach; ?>
            <div class="summary-line">
                <strong>Sous-total</strong>
                <strong><?= htmlspecialchars(number_format((float) $subtotal, 0, ',', ' ')) ?> DA</strong>
            </div>
            <p class="muted">Les frais de livraison seront ajoutés selon la wilaya et le type de livraison choisi.</p>
        </div>
    </div>
</body>
</html>

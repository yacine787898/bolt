<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - Matcha Atelier</title>
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
        .container { padding: 0 2.5rem 4rem; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 30px rgba(31, 58, 45, 0.1); }
        th, td { padding: 0.9rem; text-align: left; border-bottom: 1px solid #eef0ed; }
        th { color: #6b7d72; font-size: 0.9rem; }
        input[type="number"] { width: 80px; padding: 0.4rem; border-radius: 10px; border: 1px solid #d9dfd9; }
        .summary { margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .button { padding: 0.7rem 1.4rem; border-radius: 999px; border: none; background: var(--matcha-700); color: white; font-weight: 600; cursor: pointer; text-decoration: none; }
        .button.secondary { background: #e8eee9; color: var(--matcha-900); }
    </style>
</head>
<body>
    <header>
        <a href="/">← Continuer vos achats</a>
        <a href="/checkout">Passer commande</a>
    </header>
    <div class="container">
        <?php if (empty($items)) : ?>
            <p>Votre panier est vide pour le moment.</p>
        <?php else : ?>
            <form method="post" action="/cart/update">
                <table>
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix</th>
                            <th>Quantité</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $item['product']['name']) ?></td>
                                <td><?= htmlspecialchars(number_format((float) $item['product']['price'], 0, ',', ' ')) ?> DA</td>
                                <td>
                                    <input type="number" name="quantities[<?= htmlspecialchars((string) $item['product']['id']) ?>]" value="<?= htmlspecialchars((string) $item['quantity']) ?>" min="0">
                                </td>
                                <td><?= htmlspecialchars(number_format((float) $item['line_total'], 0, ',', ' ')) ?> DA</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="summary">
                    <div><strong>Sous-total :</strong> <?= htmlspecialchars(number_format((float) $subtotal, 0, ',', ' ')) ?> DA</div>
                    <div>
                        <button class="button secondary" type="submit">Mettre à jour</button>
                        <a class="button" href="/checkout">Commander</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

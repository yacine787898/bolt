<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merci - Matcha Atelier</title>
    <style>
        :root {
            --matcha-900: #1f3a2d;
            --matcha-700: #2f5d46;
            --matcha-200: #dff3e5;
            --cream: #f7f4ee;
        }
        body { margin: 0; font-family: "Segoe UI", sans-serif; background: var(--cream); color: var(--matcha-900); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; border-radius: 22px; padding: 2rem; width: min(520px, 90vw); box-shadow: 0 12px 30px rgba(31, 58, 45, 0.1); text-align: center; }
        .badge { display: inline-block; padding: 0.3rem 0.9rem; border-radius: 999px; background: var(--matcha-200); color: var(--matcha-700); font-weight: 600; }
        a { color: var(--matcha-700); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">Commande confirmée</span>
        <h1>Merci <?= htmlspecialchars((string) $order['customer_name']) ?> 🌿</h1>
        <p>Votre commande <strong>#<?= htmlspecialchars((string) $order['id']) ?></strong> a été enregistrée.</p>
        <p>Total payé à la livraison : <strong><?= htmlspecialchars(number_format((float) $order['total'], 0, ',', ' ')) ?> DA</strong></p>
        <p>Nous vous contacterons bientôt pour la livraison.</p>
        <p><a href="/">Retour à la boutique</a></p>
    </div>
</body>
</html>

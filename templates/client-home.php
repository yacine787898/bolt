<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande alimentaire - Client</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .restaurant { border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; }
        .badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .badge-far { background: #ffe9e9; color: #b91c1c; }
        .badge-ok { background: #ecfdf3; color: #047857; }
    </style>
</head>
<body>
    <h1>Restaurants proches</h1>
    <p><a href="/restaurant/login">Espace restaurant</a> · <a href="/admin/login">Administration</a></p>
    <p>Distance maximale avant “trop loin” : <strong><?= htmlspecialchars((string) $distanceMaxKm) ?> km</strong>.</p>

    <?php if (empty($restaurants)) : ?>
        <p>Aucun restaurant disponible pour le moment.</p>
    <?php else : ?>
        <?php foreach ($restaurants as $restaurant) : ?>
            <?php $distance = (float) ($restaurant['distance_km'] ?? 0); ?>
            <?php $isTooFar = $distance > $distanceMaxKm; ?>
            <div class="restaurant">
                <h2><?= htmlspecialchars((string) $restaurant['name']) ?></h2>
                <p>Distance estimée : <?= htmlspecialchars(number_format($distance, 1, ',', ' ')) ?> km</p>
                <?php if ($isTooFar) : ?>
                    <span class="badge badge-far">Trop loin</span>
                    <p>Livraison : prix à négocier par téléphone avec le restaurant.</p>
                <?php else : ?>
                    <span class="badge badge-ok">Livraison standard</span>
                <?php endif; ?>
                <p><a href="<?= htmlspecialchars((string) $restaurant['menu_url']) ?>">Voir le menu</a></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

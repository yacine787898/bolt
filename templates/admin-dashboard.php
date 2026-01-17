<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .panel { border: 1px solid #ddd; padding: 1rem; border-radius: 6px; }
    </style>
</head>
<body>
    <h1>Administration</h1>

    <div class="panel">
        <h2>Paramètres globaux</h2>
        <p>Distance maximale “trop loin” : <strong><?= htmlspecialchars((string) $distanceMaxKm) ?> km</strong></p>
        <p>Modifier ce paramètre via la table <code>settings</code>.</p>
    </div>

    <p>Sections à venir : gestion utilisateurs, validation restaurants, statistiques globales.</p>
</body>
</html>

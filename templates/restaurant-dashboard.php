<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Restaurant</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .stat { border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; }
    </style>
</head>
<body>
    <h1>Tableau de bord Restaurant</h1>

    <div class="stat">Visites page : <strong><?= htmlspecialchars((string) $stats['visites_page']) ?></strong></div>
    <div class="stat">Commandes passées : <strong><?= htmlspecialchars((string) $stats['commandes_passees']) ?></strong></div>
    <div class="stat">Commandes confirmées : <strong><?= htmlspecialchars((string) $stats['commandes_confirmees']) ?></strong></div>

    <p>Sections à venir : gestion des commandes, catégories, articles, suppléments et paramètres.</p>
</body>
</html>

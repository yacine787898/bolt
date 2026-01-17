<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .panel { border: 1px solid #ddd; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; }
        .badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
        .badge-ok { background: #ecfdf3; color: #047857; }
        .badge-wait { background: #fef9c3; color: #92400e; }
        .actions a { margin-right: 0.6rem; }
    </style>
</head>
<body>
    <h1>Administration</h1>
    <p><a href="/logout">Se déconnecter</a></p>

    <div class="panel">
        <h2>Paramètres globaux</h2>
        <form method="post" action="/admin/settings/distance">
            <label>
                Distance maximale “trop loin” (km)
                <input type="number" name="distance_max_km" min="1" value="<?= htmlspecialchars((string) $distanceMaxKm) ?>">
            </label>
            <button type="submit">Enregistrer</button>
        </form>
    </div>

    <div class="panel">
        <h2>Gestion restaurants (50 par page)</h2>
        <?php if (!empty($isImpersonating)) : ?>
            <p><a href="/admin/stop-impersonation">Revenir au compte admin</a></p>
        <?php endif; ?>
        <?php if (empty($restaurants)) : ?>
            <p>Aucun restaurant pour le moment.</p>
        <?php else : ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Lien menu</th>
                        <th>Clics</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($restaurants as $restaurant) : ?>
                        <?php $isValidated = (int) $restaurant['is_validated'] === 1; ?>
                        <tr>
                            <td><?= htmlspecialchars((string) $restaurant['id']) ?></td>
                            <td><?= htmlspecialchars((string) $restaurant['name']) ?></td>
                            <td><?= htmlspecialchars((string) $restaurant['email']) ?></td>
                            <td><a href="<?= htmlspecialchars((string) $restaurant['menu_url']) ?>" target="_blank" rel="noreferrer">Menu</a></td>
                            <td><?= htmlspecialchars((string) $restaurant['clicks']) ?></td>
                            <td>
                                <a class="badge <?= $isValidated ? 'badge-ok' : 'badge-wait' ?>" href="/admin/restaurants/toggle?id=<?= htmlspecialchars((string) $restaurant['id']) ?>">
                                    <?= $isValidated ? 'Validé' : 'En attente' ?>
                                </a>
                            </td>
                            <td class="actions">
                                <a href="/admin/impersonate?id=<?= htmlspecialchars((string) $restaurant['id']) ?>">Prendre l’identité</a>
                                <a href="/admin/restaurants/delete?id=<?= htmlspecialchars((string) $restaurant['id']) ?>" onclick="return confirm('Supprimer ce restaurant ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

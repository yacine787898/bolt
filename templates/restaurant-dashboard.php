<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Restaurant</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .stat { border: 1px solid #ddd; padding: 1rem; margin-bottom: 1rem; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; }
        select { padding: 0.3rem; }
    </style>
</head>
<body>
    <h1>Tableau de bord Restaurant</h1>
    <p><a href="/logout">Se déconnecter</a></p>

    <div class="stat">Visites page : <strong><?= htmlspecialchars((string) $stats['visites_page']) ?></strong></div>
    <div class="stat">Commandes passées : <strong><?= htmlspecialchars((string) $stats['commandes_passees']) ?></strong></div>
    <div class="stat">Commandes confirmées : <strong><?= htmlspecialchars((string) $stats['commandes_confirmees']) ?></strong></div>

    <?php if (!empty($isImpersonating)) : ?>
        <p><a href="/admin/stop-impersonation">Revenir au compte admin</a></p>
    <?php endif; ?>

    <h2>Commandes récentes</h2>
    <?php if (empty($orders)) : ?>
        <p>Aucune commande pour le moment.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Nom client</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th>IP client</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $order['customer_name']) ?></td>
                        <td><?= htmlspecialchars((string) $order['customer_phone']) ?></td>
                        <td><?= htmlspecialchars((string) $order['customer_address']) ?></td>
                        <td><?= htmlspecialchars((string) $order['customer_ip']) ?></td>
                        <td><?= htmlspecialchars((string) $order['status']) ?></td>
                        <td>
                            <form method="post" action="/restaurant/orders/status">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) $order['id']) ?>">
                                <select name="status">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                    <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                </select>
                                <button type="submit">Mettre à jour</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>Sections à venir : catégories, articles, suppléments et paramètres.</p>
</body>
</html>

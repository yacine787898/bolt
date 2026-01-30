<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Matcha Atelier</title>
    <style>
        :root {
            --matcha-900: #1f3a2d;
            --matcha-700: #2f5d46;
            --matcha-500: #4c8c63;
            --matcha-200: #dff3e5;
            --cream: #f7f4ee;
            --muted: #6b7d72;
        }
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background: var(--cream);
            color: var(--matcha-900);
        }
        header {
            padding: 1.5rem 2.5rem;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(31, 58, 45, 0.08);
        }
        header h1 { margin: 0; font-size: 1.4rem; }
        header a { color: var(--matcha-700); text-decoration: none; font-weight: 600; }
        main { padding: 2rem 2.5rem 4rem; display: grid; gap: 2rem; }
        .panel {
            background: white;
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(31, 58, 45, 0.08);
        }
        .panel h2 { margin-top: 0; font-size: 1.2rem; }
        .flash { background: var(--matcha-200); padding: 0.8rem 1rem; border-radius: 12px; color: var(--matcha-700); margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.7rem; text-align: left; border-bottom: 1px solid #eef0ed; vertical-align: top; }
        th { color: var(--muted); font-size: 0.9rem; }
        form.inline { display: inline; }
        .badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.2rem 0.7rem; border-radius: 999px; font-size: 0.8rem; background: var(--matcha-200); color: var(--matcha-700); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
        label { display: block; margin-top: 0.8rem; font-weight: 600; }
        input, textarea, select {
            width: 100%;
            padding: 0.6rem 0.7rem;
            border-radius: 10px;
            border: 1px solid #d9dfd9;
            background: #f9faf8;
        }
        textarea { min-height: 100px; }
        button {
            padding: 0.6rem 1rem;
            border-radius: 999px;
            border: none;
            background: var(--matcha-700);
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        .secondary { background: #e8eee9; color: var(--matcha-900); }
        .danger { background: #fee2e2; color: #991b1b; }
        .order-items { color: var(--muted); font-size: 0.9rem; }
        .order-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    </style>
</head>
<body>
    <header>
        <h1>Tableau de bord Matcha Atelier</h1>
        <a href="/admin/logout">Se déconnecter</a>
    </header>
    <main>
        <section class="panel">
            <h2>Catalogue Matcha</h2>
            <?php if (!empty($flash)) : ?>
                <div class="flash"><?= htmlspecialchars((string) $flash) ?></div>
            <?php endif; ?>
            <div class="grid">
                <form method="post" action="/admin/products/save">
                    <input type="hidden" name="id" value="">
                    <h3>Ajouter un produit</h3>
                    <label>Nom</label>
                    <input type="text" name="name" required>
                    <label>Slug (optionnel)</label>
                    <input type="text" name="slug" placeholder="matcha-ceremonial">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                    <label>Prix (DA)</label>
                    <input type="number" step="0.01" name="price" required>
                    <label>Image (URL)</label>
                    <input type="url" name="image_url" placeholder="https://">
                    <label>
                        <input type="checkbox" name="is_active" checked> Actif
                    </label>
                    <button type="submit">Enregistrer</button>
                </form>
                <div>
                    <h3>Produits existants</h3>
                    <?php if (empty($products)) : ?>
                        <p>Aucun produit.</p>
                    <?php else : ?>
                        <?php foreach ($products as $product) : ?>
                            <form method="post" action="/admin/products/save" style="border: 1px solid #eef0ed; border-radius: 14px; padding: 1rem; margin-bottom: 1rem;">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $product['id']) ?>">
                                <label>Nom</label>
                                <input type="text" name="name" value="<?= htmlspecialchars((string) $product['name']) ?>" required>
                                <label>Slug</label>
                                <input type="text" name="slug" value="<?= htmlspecialchars((string) $product['slug']) ?>">
                                <label>Description</label>
                                <textarea name="description"><?= htmlspecialchars((string) ($product['description'] ?? '')) ?></textarea>
                                <label>Prix (DA)</label>
                                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars((string) $product['price']) ?>" required>
                                <label>Image (URL)</label>
                                <input type="url" name="image_url" value="<?= htmlspecialchars((string) ($product['image_url'] ?? '')) ?>">
                                <label>
                                    <input type="checkbox" name="is_active" <?= ((int) $product['is_active'] === 1) ? 'checked' : '' ?>> Actif
                                </label>
                                <div class="order-actions">
                                    <button type="submit">Mettre à jour</button>
                                </div>
                            </form>
                            <form method="post" action="/admin/products/delete" onsubmit="return confirm('Supprimer ce produit ?');" class="inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars((string) $product['id']) ?>">
                                <button type="submit" class="danger">Supprimer</button>
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Commandes clients</h2>
            <?php if (empty($orders)) : ?>
                <p>Aucune commande pour le moment.</p>
            <?php else : ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Détails</th>
                            <th>Livraison</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td>#<?= htmlspecialchars((string) $order['id']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars((string) $order['customer_name']) ?></strong><br>
                                    <span class="order-items">📞 <?= htmlspecialchars((string) $order['customer_phone']) ?></span><br>
                                    <span class="order-items">🏠 <?= htmlspecialchars((string) $order['customer_address']) ?></span>
                                </td>
                                <td class="order-items">
                                    <?php foreach ($order['items'] as $item) : ?>
                                        <?= htmlspecialchars((string) $item['product_name']) ?> x<?= htmlspecialchars((string) $item['quantity']) ?><br>
                                    <?php endforeach; ?>
                                </td>
                                <td class="order-items">
                                    <?= htmlspecialchars((string) $order['wilaya_name']) ?><br>
                                    <?= htmlspecialchars((string) $order['delivery_type']) ?>
                                </td>
                                <td><strong><?= htmlspecialchars(number_format((float) $order['total'], 0, ',', ' ')) ?> DA</strong></td>
                                <td>
                                    <span class="badge"><?= htmlspecialchars((string) $order['status']) ?></span><br>
                                    <?php if (!empty($order['shipping_tracking'])) : ?>
                                        <span class="order-items">Tracking: <?= htmlspecialchars((string) $order['shipping_tracking']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="order-actions">
                                        <form method="post" action="/admin/orders/status" class="inline">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) $order['id']) ?>">
                                            <select name="status">
                                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                                <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmée</option>
                                                <option value="sent" <?= $order['status'] === 'sent' ? 'selected' : '' ?>>Envoyée</option>
                                            </select>
                                            <button type="submit" class="secondary">Mettre à jour</button>
                                        </form>
                                        <form method="post" action="/admin/orders/send" class="inline">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars((string) $order['id']) ?>">
                                            <button type="submit">Envoyer livraison</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>

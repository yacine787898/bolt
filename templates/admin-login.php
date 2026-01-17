<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .card { max-width: 420px; border: 1px solid #ddd; padding: 1.5rem; border-radius: 8px; }
        label { display: block; margin-top: 1rem; }
        input { width: 100%; padding: 0.5rem; margin-top: 0.4rem; }
        button { margin-top: 1rem; padding: 0.6rem 1.2rem; }
        .error { color: #b91c1c; }
    </style>
</head>
<body>
    <h1>Connexion administrateur</h1>
    <div class="card">
        <?php if (!empty($error)) : ?>
            <p class="error"><?= htmlspecialchars((string) $error) ?></p>
        <?php endif; ?>
        <form method="post" action="/admin/login">
            <label>
                Email
                <input type="email" name="email" required value="<?= htmlspecialchars((string) ($email ?? '')) ?>">
            </label>
            <label>
                Mot de passe
                <input type="password" name="password" required>
            </label>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>

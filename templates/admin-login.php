<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Matcha Atelier</title>
    <style>
        :root {
            --matcha-900: #1f3a2d;
            --matcha-700: #2f5d46;
            --matcha-500: #4c8c63;
            --matcha-200: #dff3e5;
            --cream: #f7f4ee;
        }
        body {
            font-family: "Segoe UI", sans-serif;
            margin: 0;
            background: var(--cream);
            color: var(--matcha-900);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            width: min(420px, 90vw);
            background: white;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(31, 58, 45, 0.15);
        }
        h1 { font-size: 1.6rem; margin: 0 0 1rem; }
        label { display: block; margin-top: 1rem; font-weight: 600; }
        input {
            width: 100%;
            padding: 0.75rem 0.9rem;
            margin-top: 0.4rem;
            border-radius: 12px;
            border: 1px solid #d0d9d3;
            background: #f9faf8;
        }
        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 999px;
            border: none;
            background: var(--matcha-700);
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        .error { color: #b91c1c; margin-bottom: 1rem; }
        .hint { color: #58705f; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Accès administrateur</h1>
        <p class="hint">Gérez vos produits, commandes et expéditions matcha.</p>
        <?php if (!empty($error)) : ?>
            <p class="error"><?= htmlspecialchars((string) $error) ?></p>
        <?php endif; ?>
        <form method="post" action="/admin/login">
            <label>
                Mot de passe
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>

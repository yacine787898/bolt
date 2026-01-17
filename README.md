# bolt

Plateforme de commande alimentaire (client + restaurant + admin) en PHP + MySQL.

## Démarrage rapide
1. Créer la base de données MySQL et charger le schéma.
2. Importer les données de seed.
3. Démarrer le serveur PHP.

```bash
mysql -u root -p -e "CREATE DATABASE bolt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p bolt < db/schema.sql
mysql -u root -p bolt < db/seed.sql

DB_HOST=127.0.0.1 DB_NAME=bolt DB_USER=bolt DB_PASSWORD=secret php -S 0.0.0.0:8000 public/router.php
```

- Client : http://localhost:8000/
- Restaurant : http://localhost:8000/restaurant
- Admin : http://localhost:8000/admin

## Documentation
- Spécifications fonctionnelles : [`docs/requirements.md`](docs/requirements.md)
- Questions ouvertes : [`docs/open-questions.md`](docs/open-questions.md)
- Manuel d'utilisation : [`docs/manual.md`](docs/manual.md)

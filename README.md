# Matcha Atelier

Boutique en ligne de matcha (client + admin) en PHP + MySQL.

## Démarrage rapide
1. Créer la base de données MySQL et charger le schéma.
2. Importer les données de seed (produits + wilayas).
3. Démarrer le serveur PHP.

```bash
mysql -u root -p -e "CREATE DATABASE bolt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p bolt < db/schema.sql
mysql -u root -p bolt < db/seed.sql

DB_HOST=127.0.0.1 DB_NAME=bolt DB_USER=bolt DB_PASSWORD=secret php -S 0.0.0.0:8000 -t public
```

- Boutique : http://localhost:8000/
- Admin : http://localhost:8000/admin

## Accès administrateur
- Mot de passe par défaut : `admin787898`
- Variables optionnelles :
  - `ADMIN_PASSWORD`
  - `SHIPPING_BASE_URL`
  - `SHIPPING_TOKEN`
  - `SHIPPING_KEY`

# Manuel d'utilisation (v0)

## Comptes de démonstration
Ces comptes sont chargés via `db/seed.sql`.

| Rôle | Email | Mot de passe | Usage |
| --- | --- | --- | --- |
| Admin | admin@bolt.test | admin123 | Accès interface administrateur |
| Restaurant | resto@pizzanova.test | restau123 | Accès interface restaurant |

## Accès rapide
- **Client** : `/`
- **Restaurant** : `/restaurant` (redirige vers `/restaurant/login` si non connecté)
- **Admin** : `/admin` (redirige vers `/admin/login` si non connecté)

## Parcours administrateur
1. Connectez-vous via `/admin/login` avec le compte admin.
2. Modifiez la **distance maximale “trop loin”** via le formulaire en haut de page.
3. Consultez la **liste des restaurants** (50 par page) avec les colonnes demandées.
4. Cliquez sur le badge **Validé/En attente** pour valider/invalider un restaurant.
5. Utilisez **Prendre l’identité** pour ouvrir la session restaurant sans mot de passe.
6. Cliquez **Supprimer** pour retirer un restaurant de la plateforme.

## Parcours restaurant
1. Connectez-vous via `/restaurant/login` avec le compte restaurant.
2. Consultez les statistiques de commandes sur le tableau de bord.
3. Visualisez les commandes récentes (IP client incluse).
4. Mettez à jour le statut d’une commande après confirmation téléphonique.

## Parcours client (sans compte)
1. Accédez à `/`.
2. Consultez les restaurants et leur distance.
3. Les restaurants à plus de 20 km sont marqués **Trop loin** avec mention de négociation téléphonique.

## Remarques techniques
- Pour Apache, le fichier `.htaccess` à la racine redirige les routes vers `public/index.php`.
- Pour le serveur PHP intégré, lancez :
  ```bash
  php -S 0.0.0.0:8000 -t public
  ```

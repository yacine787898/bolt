# Spécifications fonctionnelles (v0)

## Stack technique
- **Backend** : PHP 8+.
- **Base de données** : MySQL.

## Objectif
Construire une plateforme de commande alimentaire multi-interface :
- **Client** (sans authentification) : géolocalisation, sélection restaurant, commande.
- **Restaurant** (authentification) : gestion menu, commandes, paramètres.
- **Administrateur** (authentification) : gestion globale et validation.

## Paramètres de distance
- **Distance maximale par défaut** : 20 km.
- **Unité** : kilomètres.
- **Gestion** : paramètre global modifiable uniquement par l’administrateur.
- **Affichage client** : restaurants à plus de 20 km affichés comme **“trop loin”**.
- **Livraison trop loin** : prix à négocier par téléphone avec le restaurant.

## Interface Administrateur
### Tableau de bord
- Statistiques globales.
- Paramètre global : distance maximale “trop loin”.

### Gestion utilisateurs (restaurants)
- Tableau paginé (50 par page).
- Colonnes : ID auto-généré, Nom restaurant, Email, Lien menu, Nombre de clics, Statut validation, Actions.
- Actions :
  - Valider / invalider via clic sur statut.
  - Supprimer utilisateur.
  - Prendre l’identité (login sans mot de passe).
- Validation : confirmation manuelle par appel admin.

## Interface Restaurant
### Tableau de bord
- Statistiques : visites page, commandes passées, commandes confirmées.

### Gestion commandes
- Tableau, plus récentes en premier.
- Colonnes : Nom client, Actions.
- Détails : popup avec tous détails + IP client.
- Statut commande : modifiable après confirmation téléphonique client.

### Gestion catégories
- Créer, modifier, gérer ordre d’affichage.

### Gestion articles
- Articles par catégorie.
- Ajout article : popup avec champs
  - Nom, Description, Prix
  - Options (tailles pizza M/L/XXL avec prix)
  - Image (max 1MB)
  - Suppléments associés
- Actions : supprimer, désactiver temporairement, gérer ordre d’affichage.

### Gestion suppléments
- Créer, modifier, supprimer.

### Paramètres restaurant
- Prix livraison clients proches.
- Nom restaurant, détails restaurant.
- Couleurs menu, polices/fonts.
- Numéro téléphone confirmation.
- Photo principale restaurant, logo restaurant.
- Choix affichage nom ou logo.

## Interface Client
- Processus : géolocalisation → restaurants proches → menu → panier → commande → appel confirmation.
- Sans compte.
- Distance : afficher “trop loin” si > 20 km, et indiquer négociation téléphonique pour livraison.

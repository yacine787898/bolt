# Meneeto Voice (Node.js cPanel Ready)

Application Node.js/Express pour convertir du texte en audio via Gemini API, sans base de données (stockage JSON).

## Démarrage local

```bash
npm install
npm start
```

Puis ouvrir:
- Site utilisateur: `http://localhost:3000/`
- Admin: `http://localhost:3000/admin`

## Adaptation cPanel Node.js

- **Startup file**: `app.js`
- **Application root**: dossier `meneeto-voice`
- **Node.js version**: 18+ recommandé
- Lancer `npm install` puis `npm start` (ou bouton Restart App dans cPanel)

## Sécurité admin

- Mot de passe admin: `lolilol787898`
- Vérification humaine: `1 + ? = 9` (réponse: `8`)

## Fichiers JSON

- `data/access-keys.json`: clés d'accès + jetons + expiration
- `data/voice-models.json`: modèles visibles + prompts backend
- `data/settings.json`: clé API Gemini + nom du modèle
- `data/stats.json`: visites / générations / checks

## Notes

- Le texte est limité à 500 caractères côté front **et** côté API.
- Une utilisation (token) est décrémentée à chaque génération audio réussie.

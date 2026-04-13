# VFoot Arena — Interface Joueur (PHP)

Interface web PHP pour les joueurs de la plateforme.

## Responsabilités
- Inscription / connexion
- Rejoindre une cup (publique ou privée)
- Voir ses matchs et soumettre les scores
- Consulter les classements et brackets
- Profil public avec stats et trophées

## Stack
- PHP pur — architecture MVC from scratch
- Pas de framework externe
- PDO pour la base de données

## Lancer en local (XAMPP)
1. Copier le dossier `player/` dans `C:/xampp/htdocs/vfoot/player/`
2. Copier `.env.example` en `.env` et remplir les credentials
3. Importer `/database/efootball_tournament_v2.sql` dans phpMyAdmin
4. Ouvrir : `http://localhost/vfoot/player/public/`

## Base de données partagée
`efootball_tournament` — voir `/database/efootball_tournament_v2.sql`

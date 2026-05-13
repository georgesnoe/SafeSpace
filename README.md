# SafeSpace PHP (sans framework, SQLite)

Stack: PHP + SQLite + HTML/CSS

## Dossier
- `config/db.php`: connexion SQLite
- `config/helpers.php`: utilitaires + moderation
- `sql/schema_sqlite.sql`: schema SQLite
- `public/*.php`: pages de l'application

## Lancer (sans XAMPP)
1. Ouvrir un terminal dans `SafeSpace`.
2. Lancer le serveur PHP:
   - `php -S localhost:8000 -t public`
3. Initialiser la base une fois:
   - `http://localhost:8000/setup.php`
4. Ouvrir l'app:
   - `http://localhost:8000/index.php`

## Si tu vois le code PHP au lieu du rendu
Tu n'ouvres pas via un serveur PHP. Il faut passer par `php -S ...` puis URL `http://...`, pas ouvrir le fichier directement.

## Pages
- `index.php` accueil
- `feed.php` publications
- `share.php` publier anonymement
- `post.php?id=...` details + commentaires + signalement
- `inspiration.php` citations
- `private_space.php` messagerie privee MVP

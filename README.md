# Énergie Saucisse

Ce dépôt contient un petit projet PHP développé durant la deuxième année du projet CIR2. Son but est de proposer une interface web permettant d'explorer des installations photovoltaïques. On y trouve un front office pour les visiteurs avec une carte et des fonctions de recherche, ainsi qu'un back office pour que les administrateurs puissent gérer les installations.

## Structure du dépôt

- `front/` – Racine web publique avec `index.php`, les fichiers CSS/JS et les différentes vues.
- `back/` – Code PHP des contrôleurs, modèles et petite API REST dans `back/api`.
- `back/config/config.php` – Fichier de configuration des identifiants de la base de données.
- `bootstrap/` – Copie locale de Bootstrap utilisée par l'interface.
- `sql/` – Scripts SQL de création de la base pour MariaDB et PostgreSQL.
- `data/` – Exemples de données CSV.

## Configuration de la base de données

Modifiez `back/config/config.php` pour renseigner les constantes `DB_HOST`, `DB_NAME`, `DB_USER` et `DB_PASS` selon votre serveur local.

### Création de la base

Deux jeux de scripts sont fournis selon votre SGBD :

- MariaDB : `sql/MariaDb/create_db.sql` puis `sql/MariaDb/creation_tables.sql`.
- PostgreSQL : `sql/Postgresql/create_db.sql` puis `sql/Postgresql/creation_tables.sql`.

Exécutez ces scripts avec votre client SQL afin de créer l'utilisateur, la base et les tables.

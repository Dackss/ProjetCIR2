# Énergie Saucisse

This repository contains a small PHP project developed during a second year project (CIR2). The goal is to provide a web interface to explore photovoltaic installations. It offers a front office for visitors with a map and search features and a back office for administrators to manage installations.

## Repository structure

- `front/` – Public web root with `index.php`, CSS/JS assets and the different views.
- `back/` – PHP source for controllers, models and small REST API under `back/api`.
- `back/config/config.php` – Database credentials used by the application.
- `bootstrap/` – Local Bootstrap distribution used by the front end.
- `sql/` – SQL scripts to create the database. Both MariaDB and PostgreSQL versions are provided.
- `data/` – Example CSV data.

## Database configuration

Edit `back/config/config.php` and adjust `DB_HOST`, `DB_NAME`, `DB_USER` and `DB_PASS` according to your local database server.

### Creating the database

Two sets of scripts are available depending on your SGBD:

- MariaDB: `sql/MariaDb/create_db.sql` then `sql/MariaDb/creation_tables.sql`.
- PostgreSQL: `sql/Postgresql/create_db.sql` then `sql/Postgresql/creation_tables.sql`.

Execute the scripts with your SQL client to create the user, database and tables.

## Running the application

Launch the PHP built‑in server at the project root and serve the `front` directory:

```bash
php -S localhost:8000 -t front
```

You can then access the site on <http://localhost:8000>.

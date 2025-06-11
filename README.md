# Projet CIR2 - Gestion des Installations Photovoltaïques

Ce projet permet de gérer et visualiser des installations photovoltaïques via une interface web (PHP/JS) et une API REST connectée à une base de données MariaDB.

## 📁 Structure

- `front/` : Interface utilisateur (HTML, CSS, JS, PHP)
- `back/` : Contrôleurs, modèles et API REST (PHP)
- `sql/MariaDb/` : Scripts SQL de création et insertion
- `data/` : Données sources et scripts de nettoyage
- `README.md` : Ce fichier

## 🚀 Lancer le projet

1. Importer les scripts SQL dans MariaDB :
   ```bash
   mysql -u root -p projetCIR2 < sql/MariaDb/création.sql
   mysql -u root -p projetCIR2 < sql/MariaDb/insertion.sql
   ```

2. Lancer le serveur de votre choix :
 

3. Accéder à votre serveur

## 🔐 Partie Admin

- Connexion via `/index.php?page=AdminConnexion`
- Gestion des installations, recherche filtrée.

## 🔗 Dépôt GitHub

[https://github.com/Dackss/ProjetCIR2](https://github.com/Dackss/ProjetCIR2)

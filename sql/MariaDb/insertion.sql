-- =======================
-- IMPORT DES COMMUNES
-- =======================

-- On crée une table temporaire (staging) pour charger les données brutes
DROP TABLE IF EXISTS StagingCommune;
CREATE TABLE StagingCommune (
                                code_insee VARCHAR(10),
                                nom_commune VARCHAR(255),
                                code_region VARCHAR(10),
                                nom_region VARCHAR(255),
                                code_departement VARCHAR(10),
                                nom_departement VARCHAR(255),
                                code_postal VARCHAR(10),
                                population VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- On charge les données CSV dans la table staging
LOAD DATA LOCAL INFILE 'C:/Users/to1ca/PhpstormProjects/TP_php/GitHub/ProjetCIR2/sql/MariaDb/communes-france-2024-limite.csv'
    INTO TABLE StagingCommune
    FIELDS TERMINATED BY ';' ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 LINES (
                    code_insee, nom_commune, code_region, nom_region,
                    code_departement, nom_departement, code_postal, population
        );

-- On désactive les contraintes de clés étrangères pour permettre les TRUNCATE
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Commune;
TRUNCATE TABLE Département;
TRUNCATE TABLE Région;
SET FOREIGN_KEY_CHECKS = 1;

-- On insère les régions uniques
INSERT IGNORE INTO Région (id_region, nom_region)
SELECT code_region, MIN(nom_region)
FROM StagingCommune
WHERE code_region IS NOT NULL
GROUP BY code_region;

-- On insère les départements
INSERT IGNORE INTO Département (code_departement, nom_departement, id_region_Région)
SELECT DISTINCT code_departement, nom_departement, code_region
FROM StagingCommune
WHERE code_departement IS NOT NULL AND nom_departement IS NOT NULL AND code_region IS NOT NULL;

-- On insère les communes
INSERT IGNORE INTO Commune (code_insee, nom_commune, population, code_postal, code_departement_Département)
SELECT DISTINCT
    code_insee,
    nom_commune,

    -- Nettoyage de la population :
    -- 1. REGEXP_REPLACE(population, '[^0-9]', '') : supprime tous les caractères non numériques,
    --    par exemple "12 345 hab." devient "12345".
    -- 2. CAST(... AS UNSIGNED) : convertit la chaîne obtenue en entier positif (UNSIGNED = sans valeur négative).
    --    Cela permet de garantir un champ propre même si la donnée initiale contenait du texte ou des symboles.
    CAST(REGEXP_REPLACE(population, '[^0-9]', '') AS UNSIGNED),

    -- Nettoyage du code postal :
    -- Même principe que ci-dessus :
    -- 1. On retire tous les caractères non numériques pour ne garder que les chiffres.
    -- 2. On convertit ensuite la chaîne en entier positif.
    -- Exemple : "75000-Paris" devient "75000"
    CAST(REGEXP_REPLACE(code_postal, '[^0-9]', '') AS UNSIGNED),

    -- Pas de traitement ici car code_departement est déjà propre dans le fichier.
    code_departement

FROM StagingCommune

-- On filtre les lignes invalides : on ignore celles sans code INSEE ou sans département
WHERE code_insee IS NOT NULL AND code_departement IS NOT NULL;

-- =======================
-- IMPORT DES INSTALLATIONS
-- =======================

-- Table temporaire pour stocker les données brutes
DROP TABLE IF EXISTS StagingInstall;
CREATE TABLE StagingInstall (
                                id INT,
                                iddoc INT,
                                mois_installation VARCHAR(2),
                                an_installation VARCHAR(4),
                                nb_panneaux INT,
                                panneaux_marque VARCHAR(255),
                                panneaux_modele VARCHAR(255),
                                nb_onduleur INT,
                                onduleur_marque VARCHAR(255),
                                onduleur_modele VARCHAR(255),
                                puissance_crete VARCHAR(20),
                                surface VARCHAR(20),
                                pente VARCHAR(10),
                                pente_optimum VARCHAR(10),
                                orientation VARCHAR(20),
                                orientation_optimum VARCHAR(20),
                                installateur VARCHAR(255),
                                production_pvgis VARCHAR(20),
                                lat VARCHAR(20),
                                lon VARCHAR(20),
                                country VARCHAR(100),
                                postal_code VARCHAR(10),
                                postal_code_suffix VARCHAR(10),
                                postal_town VARCHAR(255),
                                locality VARCHAR(255),
                                administrative_area_level_1 VARCHAR(255),
                                administrative_area_level_2 VARCHAR(255),
                                administrative_area_level_3 VARCHAR(255),
                                administrative_area_level_4 VARCHAR(255),
                                political VARCHAR(255),
                                code_insee_commune VARCHAR(10),
                                id_panneau INT,
                                id_onduleur INT,
                                id_installateur INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chargement brut des données dans la table staging
LOAD DATA LOCAL INFILE 'C:/Users/to1ca/PhpstormProjects/TP_php/GitHub/ProjetCIR2/sql/MariaDb/data_clean.csv'
    INTO TABLE StagingInstall
    FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 LINES;

-- On vide les tables de référence pour réinitialisation
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Installation;
TRUNCATE TABLE Installateur;
TRUNCATE TABLE Panneau;
TRUNCATE TABLE ModelePanneau;
TRUNCATE TABLE MarquePanneau;
TRUNCATE TABLE Onduleur;
TRUNCATE TABLE ModeleOnduleur;
TRUNCATE TABLE MarqueOnduleur;
SET FOREIGN_KEY_CHECKS = 1;

-- Création des index nécessaires pour optimiser les jointures entre StagingInstall et les autres tables
-- Utilisé pour retrouver rapidement une commune à partir du nom (locality) et du code postal
CREATE INDEX idx_staging_postal_locality ON StagingInstall(postal_code, locality);
-- Permet de filtrer rapidement les lignes de StagingInstall déjà liées à une commune
CREATE INDEX idx_staging_code_insee_commune ON StagingInstall(code_insee_commune);
-- Utilisé pour faire correspondre les panneaux de la staging avec les modèles enregistrés
CREATE INDEX idx_staging_panneau_modele ON StagingInstall(panneaux_modele);
-- Permet de retrouver les onduleurs dans les jointures avec la table Onduleur
CREATE INDEX idx_staging_onduleur_modele ON StagingInstall(onduleur_modele);
-- Utilisé pour associer les installateurs de la staging à ceux de la table principale
CREATE INDEX idx_staging_installateur_nom ON StagingInstall(installateur);

-- Index sur les tables principales pour accélérer les jointures avec la staging
-- Sert à faire le lien entre nom de commune + code postal et code INSEE
CREATE INDEX idx_commune_nom_postal ON Commune(nom_commune, code_postal);
-- Permet de retrouver un panneau à partir de son modèle
CREATE INDEX idx_panneau_modele ON Panneau(modele_panneau);
-- Permet de retrouver un onduleur à partir de son modèle
CREATE INDEX idx_onduleur_modele ON Onduleur(modele_onduleur);
-- Permet de retrouver un installateur à partir de son nom
CREATE INDEX idx_installateur_nom ON Installateur(nom_installateur);

-- Remplissage des tables de référence
INSERT IGNORE INTO MarquePanneau (nom_marque)
SELECT DISTINCT panneaux_marque FROM StagingInstall WHERE panneaux_marque IS NOT NULL;

INSERT IGNORE INTO ModelePanneau (nom_modele)
SELECT DISTINCT panneaux_modele FROM StagingInstall WHERE panneaux_modele IS NOT NULL;

INSERT IGNORE INTO Panneau (modele_panneau, id_marque_MarquePanneau, id_modele_ModelePanneau)
SELECT DISTINCT si.panneaux_modele, mp.id_marque, mo.id_modele
FROM StagingInstall si
         JOIN MarquePanneau mp ON si.panneaux_marque = mp.nom_marque
         JOIN ModelePanneau mo ON si.panneaux_modele = mo.nom_modele;

INSERT IGNORE INTO MarqueOnduleur (nom_marque)
SELECT DISTINCT onduleur_marque FROM StagingInstall WHERE onduleur_marque IS NOT NULL;

INSERT IGNORE INTO ModeleOnduleur (nom_modele)
SELECT DISTINCT onduleur_modele FROM StagingInstall WHERE onduleur_modele IS NOT NULL;

INSERT IGNORE INTO Onduleur (modele_onduleur, id_marque_MarqueOnduleur, id_modele_ModeleOnduleur)
SELECT DISTINCT si.onduleur_modele, mo.id_marque, mo2.id_modele
FROM StagingInstall si
         JOIN MarqueOnduleur mo ON si.onduleur_marque = mo.nom_marque
         JOIN ModeleOnduleur mo2 ON si.onduleur_modele = mo2.nom_modele;

INSERT IGNORE INTO Installateur (nom_installateur)
SELECT DISTINCT installateur FROM StagingInstall WHERE installateur IS NOT NULL;

-- Associe les lignes de StagingInstall à une commune en retrouvant son code INSEE
-- via le nom de la commune (locality) + le code postal.
UPDATE StagingInstall si
    JOIN Commune c
    ON
        -- Compare le nom de commune de la staging avec celui de la base (insensible à la casse et aux accents)
        -- COLLATE utf8mb4_general_ci : permet de faire une comparaison plus souple entre strings,
        -- exemple : "saint-malo" = "Saint-Malo"
        si.locality COLLATE utf8mb4_general_ci = c.nom_commune COLLATE utf8mb4_general_ci

            -- On convertit le code postal en entier pour comparer avec celui de la table Commune
            -- si postal_code est vide ou contient des lettres, le CAST échouerait
            AND CAST(si.postal_code AS UNSIGNED) = c.code_postal

-- On met à jour uniquement les lignes qui n’ont pas encore de code INSEE associé
-- REGEXP '^[0-9]+$' : on filtre les codes postaux qui ne contiennent que des chiffres
-- ^    → début de la chaîne
-- [0-9] → chiffre de 0 à 9
-- +    → un ou plusieurs chiffres
-- $    → fin de la chaîne
-- Exemple : "75000" = OK, "75-000" = ignoré
SET si.code_insee_commune = c.code_insee
WHERE si.code_insee_commune IS NULL
  AND si.locality IS NOT NULL
  AND si.postal_code REGEXP '^[0-9]+$'; -- https://fr.wikibooks.org/wiki/MySQL/Regex


UPDATE StagingInstall si
    JOIN Panneau p ON si.panneaux_modele = p.modele_panneau
SET si.id_panneau = p.id_panneau
WHERE si.id_panneau IS NULL;

UPDATE StagingInstall si
    JOIN Onduleur o ON si.onduleur_modele = o.modele_onduleur
SET si.id_onduleur = o.id_onduleur
WHERE si.id_onduleur IS NULL;

UPDATE StagingInstall si
    JOIN Installateur i ON si.installateur = i.nom_installateur
SET si.id_installateur = i.id_installateur
WHERE si.id_installateur IS NULL;

-- Insertion finale dans Installation
INSERT IGNORE INTO Installation (
    date_installation, nb_panneaux, nb_onduleur,
    surface, puissance, latitude, longitude,
    pente, pente_optimum,
    orientation, orientation_optimum,
    production_pvgis,
    id_onduleur_Onduleur,
    id_installateur_Installateur,
    id_panneau_Panneau,
    code_insee_Commune
)
SELECT
    -- Construit une vraie DATE SQL à partir de l’année et du mois (issus de deux colonnes séparées)
    -- CONCAT(..., '-01') : on ajoute le jour "01" pour former une date complète
    -- LPAD(...) : ajoute un zéro si le mois n’a qu’un chiffre (ex : "5" devient "05")
    -- STR_TO_DATE(...) : convertit la chaîne finale en type DATE MySQL
    STR_TO_DATE(CONCAT(si.an_installation, '-', LPAD(si.mois_installation, 2, '0'), '-01'), '%Y-%m-%d'),

    -- Données déjà numériques, pas besoin de transformation
    si.nb_panneaux,
    si.nb_onduleur,

    -- Nettoyage de la surface :
    -- 1. Remplace les virgules par des points pour que MySQL comprenne les décimaux
    -- 2. Supprime les espaces
    -- 3. NULLIF(..., '') : si le champ devient vide, retourne NULL pour éviter une erreur
    -- 4. CAST en DECIMAL(8,2) : max 999999.99
    CAST(NULLIF(REPLACE(REPLACE(si.surface, ',', '.'), ' ', ''), '') AS DECIMAL(8,2)),

    -- Même principe pour la puissance crête (kWc)
    CAST(NULLIF(REPLACE(REPLACE(si.puissance_crete, ',', '.'), ' ', ''), '') AS DECIMAL(8,2)),

    -- Latitude et longitude : nettoyage + conversion en nombre à 6 décimales (type GPS)
    CAST(NULLIF(REPLACE(REPLACE(si.lat, ',', '.'), ' ', ''), '') AS DECIMAL(9,6)),
    CAST(NULLIF(REPLACE(REPLACE(si.lon, ',', '.'), ' ', ''), '') AS DECIMAL(9,6)),

    -- Nettoyage de la pente (exprimée en degrés) + conversion
    CAST(NULLIF(REPLACE(REPLACE(si.pente, ',', '.'), ' ', ''), '') AS DECIMAL(4,1)),
    CAST(NULLIF(REPLACE(REPLACE(si.pente_optimum, ',', '.'), ' ', ''), '') AS DECIMAL(4,1)),

    -- Traitement de l’orientation :
    -- Certaines valeurs sont en texte (ex : "sud", "nord"), d’autres sont déjà en angle (ex : "180").
    -- On traduit les textes en degrés selon la convention suivante :
    --   - sud    → 180°
    --   - nord   → 0°
    --   - est    → 90°
    --   - ouest  → 270°
    -- Si la valeur est déjà un angle numérique (ex : "135", "-30"), on le garde tel quel.
    -- Sinon (ex : "inconnu", vide, etc.), on insère NULL.
    CAST(
            CASE
                -- LOWER(...) rend la valeur insensible à la casse : "SUD", "sud", "Sud" → "sud"
                WHEN LOWER(si.orientation) = 'sud' THEN 180
                WHEN LOWER(si.orientation) = 'nord' THEN 0
                WHEN LOWER(si.orientation) = 'est' THEN 90
                WHEN LOWER(si.orientation) = 'ouest' THEN 270

                -- REGEXP '^-?[0-9]+$' permet de vérifier si la valeur est un nombre entier :
                -- ^        : début de la chaîne
                -- -?       : 0 ou 1 signe moins (valeurs négatives possibles)
                -- [0-9]+   : une ou plusieurs chiffres
                -- $        : fin de la chaîne
                -- Exemple : "180", "-30", "0" = OK / "30.5", "sud", "" = refusé
                WHEN si.orientation REGEXP '^-?[0-9]+$' THEN si.orientation

                -- Sinon on met NULL
                ELSE NULL
                END
        AS SIGNED -- On convertit explicitement le résultat final en entier signé (type INT avec valeurs négatives autorisées)
    ),
    -- Même traitement pour l’orientation optimale
    CAST(
            CASE
                WHEN LOWER(si.orientation_optimum) = 'sud' THEN 180
                WHEN LOWER(si.orientation_optimum) = 'nord' THEN 0
                WHEN LOWER(si.orientation_optimum) = 'est' THEN 90
                WHEN LOWER(si.orientation_optimum) = 'ouest' THEN 270
                WHEN si.orientation_optimum REGEXP '^-?[0-9]+$' THEN si.orientation_optimum
                ELSE NULL
                END
        AS SIGNED
    ),


    -- Nettoyage + conversion de la production PVGIS (production annuelle estimée en kWh)
    CAST(NULLIF(REPLACE(REPLACE(si.production_pvgis, ',', '.'), ' ', ''), '') AS DECIMAL(8,2)),

    -- Clés étrangères déjà résolues dans la table de staging
    si.id_onduleur,
    si.id_installateur,
    si.id_panneau,
    si.code_insee_commune

FROM StagingInstall si

-- On insère uniquement les lignes pour lesquelles toutes les clés étrangères nécessaires sont déjà connues
WHERE si.id_onduleur IS NOT NULL
  AND si.id_installateur IS NOT NULL
  AND si.id_panneau IS NOT NULL;

-- Insertion de l'utilisateur admin de base
INSERT IGNORE INTO Admin (identifiant, mot_de_passe) VALUES ('admin', 'tk78');

-- Nettoyage final
DROP TABLE IF EXISTS StagingCommune;
DROP TABLE IF EXISTS StagingInstall;

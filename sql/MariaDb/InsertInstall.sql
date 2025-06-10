-- Réinitialisation des tables cibles
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

-- Index nécessaires
DROP INDEX IF EXISTS idx_staging_panneau_modele ON StagingInstall;
DROP INDEX IF EXISTS idx_staging_onduleur_modele ON StagingInstall;
DROP INDEX IF EXISTS idx_staging_installateur_nom ON StagingInstall;
DROP INDEX IF EXISTS idx_staging_postal_locality ON StagingInstall;
DROP INDEX IF EXISTS idx_staging_code_insee_commune ON StagingInstall;

DROP INDEX IF EXISTS idx_panneau_modele ON Panneau;
DROP INDEX IF EXISTS idx_onduleur_modele ON Onduleur;
DROP INDEX IF EXISTS idx_installateur_nom ON Installateur;
DROP INDEX IF EXISTS idx_commune_nom_postal ON Commune;
DROP INDEX IF EXISTS idx_commune_code_postal ON Commune;

CREATE INDEX idx_staging_panneau_modele ON StagingInstall(panneaux_modele);
CREATE INDEX idx_staging_onduleur_modele ON StagingInstall(onduleur_modele);
CREATE INDEX idx_staging_installateur_nom ON StagingInstall(installateur);
CREATE INDEX idx_staging_postal_locality ON StagingInstall(postal_code, locality);
CREATE INDEX idx_staging_code_insee_commune ON StagingInstall(code_insee_commune);

CREATE INDEX idx_panneau_modele ON Panneau(modele_panneau);
CREATE INDEX idx_onduleur_modele ON Onduleur(modele_onduleur);
CREATE INDEX idx_installateur_nom ON Installateur(nom_installateur);
CREATE INDEX idx_commune_nom_postal ON Commune(nom_commune, code_postal);
CREATE INDEX idx_commune_code_postal ON Commune(code_postal);

-- Marque / Modèle / Panneau
INSERT IGNORE INTO MarquePanneau (nom_marque)
SELECT DISTINCT panneaux_marque FROM StagingInstall WHERE panneaux_marque IS NOT NULL;

INSERT IGNORE INTO ModelePanneau (nom_modele)
SELECT DISTINCT panneaux_modele FROM StagingInstall WHERE panneaux_modele IS NOT NULL;

INSERT IGNORE INTO Panneau (modele_panneau, id_marque_MarquePanneau, id_modele_ModelePanneau)
SELECT DISTINCT si.panneaux_modele, mp.id_marque, mo.id_modele
FROM StagingInstall si
         JOIN MarquePanneau mp ON si.panneaux_marque = mp.nom_marque
         JOIN ModelePanneau mo ON si.panneaux_modele = mo.nom_modele;

-- Marque / Modèle / Onduleur
INSERT IGNORE INTO MarqueOnduleur (nom_marque)
SELECT DISTINCT onduleur_marque FROM StagingInstall WHERE onduleur_marque IS NOT NULL;

INSERT IGNORE INTO ModeleOnduleur (nom_modele)
SELECT DISTINCT onduleur_modele FROM StagingInstall WHERE onduleur_modele IS NOT NULL;

INSERT IGNORE INTO Onduleur (modele_onduleur, id_marque_MarqueOnduleur, id_modele_ModeleOnduleur)
SELECT DISTINCT si.onduleur_modele, mo.id_marque, mo2.id_modele
FROM StagingInstall si
         JOIN MarqueOnduleur mo ON si.onduleur_marque = mo.nom_marque
         JOIN ModeleOnduleur mo2 ON si.onduleur_modele = mo2.nom_modele;

-- Installateurs
INSERT IGNORE INTO Installateur (nom_installateur)
SELECT DISTINCT installateur FROM StagingInstall WHERE installateur IS NOT NULL;

-- Matching commune (insee)
UPDATE StagingInstall si
    JOIN Commune c
    ON si.locality COLLATE utf8mb4_general_ci = c.nom_commune COLLATE utf8mb4_general_ci
        AND CAST(si.postal_code AS UNSIGNED) = c.code_postal
SET si.code_insee_commune = c.code_insee
WHERE si.code_insee_commune IS NULL
  AND si.locality IS NOT NULL AND si.postal_code REGEXP '^[0-9]+$';

-- Matching panneau / onduleur / installateur ID
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

-- Insertion dans Installation (même sans code_insee_commune)
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
    STR_TO_DATE(CONCAT(si.an_installation, '-', LPAD(si.mois_installation, 2, '0'), '-01'), '%Y-%m-%d'),
    CAST(si.nb_panneaux AS UNSIGNED),
    CAST(si.nb_onduleur AS UNSIGNED),

    -- surface
    CAST(
            NULLIF(REPLACE(REPLACE(si.surface, ',', '.'), ' ', ''), '')
        AS DECIMAL(8,2)
    ),

    -- puissance
    CAST(
            NULLIF(REPLACE(REPLACE(si.puissance_crete, ',', '.'), ' ', ''), '')
        AS DECIMAL(8,2)
    ),

    -- lat/lon
    CAST(NULLIF(REPLACE(REPLACE(si.lat, ',', '.'), ' ', ''), '') AS DECIMAL(9,6)),
    CAST(NULLIF(REPLACE(REPLACE(si.lon, ',', '.'), ' ', ''), '') AS DECIMAL(9,6)),

    -- pente
    CAST(NULLIF(REPLACE(REPLACE(si.pente, ',', '.'), ' ', ''), '') AS DECIMAL(4,1)),
    CAST(NULLIF(REPLACE(REPLACE(si.pente_optimum, ',', '.'), ' ', ''), '') AS DECIMAL(4,1)),

    -- orientation
    CAST(
            CASE
                WHEN LOWER(si.orientation) = 'sud' THEN 180
                WHEN LOWER(si.orientation) = 'nord' THEN 0
                WHEN LOWER(si.orientation) = 'est' THEN 90
                WHEN LOWER(si.orientation) = 'ouest' THEN 270
                WHEN si.orientation REGEXP '^-?[0-9]+$' THEN si.orientation
                ELSE NULL
                END AS SIGNED
    ),

    -- orientation_optimum
    CAST(
            CASE
                WHEN LOWER(si.orientation_optimum) = 'sud' THEN 180
                WHEN LOWER(si.orientation_optimum) = 'nord' THEN 0
                WHEN LOWER(si.orientation_optimum) = 'est' THEN 90
                WHEN LOWER(si.orientation_optimum) = 'ouest' THEN 270
                WHEN si.orientation_optimum REGEXP '^-?[0-9]+$' THEN si.orientation_optimum
                ELSE NULL
                END AS SIGNED
    ),

    -- production
    CAST(NULLIF(REPLACE(REPLACE(si.production_pvgis, ',', '.'), ' ', ''), '') AS DECIMAL(8,2)),

    -- FKs
    si.id_onduleur,
    si.id_installateur,
    si.id_panneau,
    si.code_insee_commune

FROM StagingInstall si
WHERE si.id_onduleur IS NOT NULL
  AND si.id_installateur IS NOT NULL
  AND si.id_panneau IS NOT NULL;

INSERT INTO Admin (identifiant, mot_de_passe) VALUES ('admin', 'tk78');

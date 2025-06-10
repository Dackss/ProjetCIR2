DROP TABLE IF EXISTS StagingCommune;

CREATE TABLE StagingCommune (
                                code_insee VARCHAR(10),
                                nom_commune VARCHAR(255),
                                code_region VARCHAR(10),
                                nom_region VARCHAR(255),
                                code_departement VARCHAR(10),
                                nom_departement VARCHAR(255),
                                code_postal VARCHAR(10),
                                population VARCHAR(20)  -- Changement ici
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

LOAD DATA LOCAL INFILE 'communes-france-2024-limite.csv'
    INTO TABLE StagingCommune
         FIELDS TERMINATED BY ';'
    ENCLOSED BY '"'
    LINES TERMINATED BY '\n'
    IGNORE 1 LINES
    (
     code_insee,
     nom_commune,
     code_region,
     nom_region,
     code_departement,
     nom_departement,
     code_postal,
     population
        );

SELECT code_region, COUNT(DISTINCT nom_region) AS nb
FROM StagingCommune
GROUP BY code_region
HAVING nb > 1;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE Commune;
TRUNCATE TABLE Département;
TRUNCATE TABLE Région;

SET FOREIGN_KEY_CHECKS = 1;

INSERT IGNORE INTO Région (id_region, nom_region)
SELECT code_region, MIN(nom_region)
FROM StagingCommune
WHERE code_region IS NOT NULL
GROUP BY code_region;

INSERT IGNORE INTO Département (code_departement, nom_departement, id_region_Région)
SELECT DISTINCT
    code_departement,
    nom_departement,
    code_region
FROM StagingCommune
WHERE code_departement IS NOT NULL
  AND nom_departement IS NOT NULL
  AND code_region IS NOT NULL;

INSERT IGNORE INTO Commune (code_insee, nom_commune, population, code_postal, code_departement_Département)
SELECT DISTINCT
    code_insee,
    nom_commune,
    CAST(REGEXP_REPLACE(population, '[^0-9]', '') AS UNSIGNED),
    CAST(REGEXP_REPLACE(code_postal, '[^0-9]', '') AS UNSIGNED),
    code_departement
FROM StagingCommune
WHERE code_insee IS NOT NULL
  AND code_departement IS NOT NULL;
DROP TABLE IF EXISTS installation;
DROP TABLE IF EXISTS installateur;
DROP TABLE IF EXISTS panneau;
DROP TABLE IF EXISTS onduleur;
DROP TABLE IF EXISTS commune;

-- Table Installateur
CREATE TABLE installateur (
    id_installateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) UNIQUE
);

-- Table Panneau
CREATE TABLE panneau (
    id_panneau INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(100),
    modele VARCHAR(100),
    UNIQUE (marque, modele)
);

-- Table Onduleur
CREATE TABLE onduleur (
    id_onduleur INT AUTO_INCREMENT PRIMARY KEY,
    marque VARCHAR(100),
    modele VARCHAR(100),
    UNIQUE (marque, modele)
);

-- Table Commune (code_insee = code postal sans suffixe)
CREATE TABLE commune (
    id_commune INT AUTO_INCREMENT PRIMARY KEY,
    code_postal VARCHAR(10),
    code_postal_suffix VARCHAR(10),
    nom_commune VARCHAR(100),
    localite VARCHAR(100),
    niveau1 VARCHAR(100),
    niveau2 VARCHAR(100),
    niveau3 VARCHAR(100),
    niveau4 VARCHAR(100),
    pays VARCHAR(50)
);

-- Table Installation
CREATE TABLE installation (
    id_installation INT PRIMARY KEY,
    iddoc INT,
    mois_installation VARCHAR(2),
    an_installation INT,
    nb_panneaux INT,
    id_panneau INT,
    nb_onduleur INT,
    id_onduleur INT,
    puissance_crete DECIMAL(8,2),
    surface DECIMAL(6,2),
    pente DECIMAL(4,1),
    pente_optimum DECIMAL(4,1),
    orientation SMALLINT,
    orientation_optimum SMALLINT,
    id_installateur INT,
    production_pvgis DECIMAL(8,2),
    lat DECIMAL(9,6),
    lon DECIMAL(9,6),
    id_commune INT,
    political VARCHAR(100),
    FOREIGN KEY (id_installateur) REFERENCES installateur(id_installateur),
    FOREIGN KEY (id_panneau) REFERENCES panneau(id_panneau),
    FOREIGN KEY (id_onduleur) REFERENCES onduleur(id_onduleur),
    FOREIGN KEY (id_commune) REFERENCES commune(id_commune)
);

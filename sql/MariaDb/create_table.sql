DROP TABLE IF EXISTS InstallationPanneau;
DROP TABLE IF EXISTS InstallationOnduleur;
DROP TABLE IF EXISTS CommuneCodePostal;
DROP TABLE IF EXISTS Installation;
DROP TABLE IF EXISTS Commune;
DROP TABLE IF EXISTS Departement;
DROP TABLE IF EXISTS Region;
DROP TABLE IF EXISTS MarquePanneau;
DROP TABLE IF EXISTS ModelePanneau;
DROP TABLE IF EXISTS Panneau;
DROP TABLE IF EXISTS Installateur;
DROP TABLE IF EXISTS MarqueOnduleur;
DROP TABLE IF EXISTS ModeleOnduleur;
DROP TABLE IF EXISTS Onduleur;
DROP TABLE IF EXISTS CodePostal;

CREATE TABLE Region (
    id_region VARCHAR(25) PRIMARY KEY,
    nom_region VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Departement (
    code_departement VARCHAR(25) PRIMARY KEY,
    nom_departement VARCHAR(100),
    id_region VARCHAR(25),
    FOREIGN KEY (id_region) REFERENCES Region(id_region)
) ENGINE=InnoDB;

CREATE TABLE Commune (
    code_insee VARCHAR(10) PRIMARY KEY,
    nom_commune VARCHAR(100),
    population INT,
    code_departement VARCHAR(25),
    FOREIGN KEY (code_departement) REFERENCES Departement(code_departement)
) ENGINE=InnoDB;

CREATE TABLE CodePostal (
    code_postal INT PRIMARY KEY
) ENGINE=InnoDB;

CREATE TABLE CommuneCodePostal (
    code_insee VARCHAR(10),
    code_postal INT,
    PRIMARY KEY (code_insee, code_postal),
    FOREIGN KEY (code_insee) REFERENCES Commune(code_insee),
    FOREIGN KEY (code_postal) REFERENCES CodePostal(code_postal)
) ENGINE=InnoDB;

CREATE TABLE MarquePanneau (
    id_marque INT AUTO_INCREMENT PRIMARY KEY,
    nom_marque VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE ModelePanneau (
    id_modele INT AUTO_INCREMENT PRIMARY KEY,
    nom_modele VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Panneau (
    id_panneau INT AUTO_INCREMENT PRIMARY KEY,
    id_marque INT,
    id_modele INT,
    FOREIGN KEY (id_marque) REFERENCES MarquePanneau(id_marque),
    FOREIGN KEY (id_modele) REFERENCES ModelePanneau(id_modele)
) ENGINE=InnoDB;

CREATE TABLE MarqueOnduleur (
    id_marque INT AUTO_INCREMENT PRIMARY KEY,
    nom_marque VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE ModeleOnduleur (
    id_modele INT AUTO_INCREMENT PRIMARY KEY,
    nom_modele VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Onduleur (
    id_onduleur INT AUTO_INCREMENT PRIMARY KEY,
    id_marque INT,
    id_modele INT,
    FOREIGN KEY (id_marque) REFERENCES MarqueOnduleur(id_marque),
    FOREIGN KEY (id_modele) REFERENCES ModeleOnduleur(id_modele)
) ENGINE=InnoDB;

CREATE TABLE Installateur (
    id_installateur INT AUTO_INCREMENT PRIMARY KEY,
    nom_installateur VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Installation (
    id_installation INT AUTO_INCREMENT PRIMARY KEY,
    date_installation DATE,
    nb_panneaux INT,
    surface DECIMAL(6,2),
    puissance DECIMAL(6,2),
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    pente DECIMAL(4,1),
    pente_optimum DECIMAL(4,1),
    orientation SMALLINT,
    orientation_optimum SMALLINT,
    production_pvgis DECIMAL(8,2),
    id_installateur INT,
    code_insee VARCHAR(10),
    FOREIGN KEY (id_installateur) REFERENCES Installateur(id_installateur),
    FOREIGN KEY (code_insee) REFERENCES Commune(code_insee)
) ENGINE=InnoDB;

CREATE TABLE InstallationOnduleur (
    id_installation INT,
    id_onduleur INT,
    quantite_onduleurs SMALLINT,
    PRIMARY KEY (id_installation, id_onduleur),
    FOREIGN KEY (id_installation) REFERENCES Installation(id_installation),
    FOREIGN KEY (id_onduleur) REFERENCES Onduleur(id_onduleur)
) ENGINE=InnoDB;

CREATE TABLE InstallationPanneau (
    id_installation INT,
    id_panneau INT,
    quantite_panneaux SMALLINT,
    PRIMARY KEY (id_installation, id_panneau),
    FOREIGN KEY (id_installation) REFERENCES Installation(id_installation),
    FOREIGN KEY (id_panneau) REFERENCES Panneau(id_panneau)
) ENGINE=InnoDB;

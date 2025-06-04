DROP TABLE IF EXISTS Installation;
DROP TABLE IF EXISTS Commune;
DROP TABLE IF EXISTS Département;
DROP TABLE IF EXISTS Région;
DROP TABLE IF EXISTS MarquePanneau;
DROP TABLE IF EXISTS Panneau;
DROP TABLE IF EXISTS Installateur;
DROP TABLE IF EXISTS Onduleur;
DROP TABLE IF EXISTS ModeleOnduleur;
DROP TABLE IF EXISTS MarqueOnduleur;
DROP TABLE IF EXISTS ModelePanneau;

CREATE TABLE Région (
                        id_region VARCHAR(25) PRIMARY KEY,
                        nom_region VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE Département (
                             code_departement VARCHAR(10) PRIMARY KEY,
                             nom_departement VARCHAR(100),
                             id_region VARCHAR(25),
                             FOREIGN KEY (id_region) REFERENCES Région(id_region)
) ENGINE=InnoDB;

CREATE TABLE Commune (
                         code_insee VARCHAR(10) PRIMARY KEY,
                         nom_commune VARCHAR(100),
                         population INT,
                         code_postal INT,
                         code_departement VARCHAR(10),
                         FOREIGN KEY (code_departement) REFERENCES Département(code_departement)
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
                         modele_panneau VARCHAR(100),
                         id_marque INT,
                         id_modele INT,
                         FOREIGN KEY (id_marque) REFERENCES MarquePanneau(id_marque),
                         FOREIGN KEY (id_modele) REFERENCES ModelePanneau(id_modele)
) ENGINE=InnoDB;

CREATE TABLE Installateur (
                              id_installateur INT AUTO_INCREMENT PRIMARY KEY,
                              nom_installateur VARCHAR(100)
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
                          modele_onduleur VARCHAR(100),
                          id_marque INT,
                          id_modele INT,
                          FOREIGN KEY (id_marque) REFERENCES MarqueOnduleur(id_marque),
                          FOREIGN KEY (id_modele) REFERENCES ModeleOnduleur(id_modele)
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
                              quantite_onduleurs SMALLINT,
                              id_onduleur INT,
                              id_installateur INT,
                              quantite_panneaux SMALLINT,
                              id_panneau INT,
                              code_insee VARCHAR(10),
                              FOREIGN KEY (id_onduleur) REFERENCES Onduleur(id_onduleur),
                              FOREIGN KEY (id_installateur) REFERENCES Installateur(id_installateur),
                              FOREIGN KEY (id_panneau) REFERENCES Panneau(id_panneau),
                              FOREIGN KEY (code_insee) REFERENCES Commune(code_insee)
) ENGINE=InnoDB;

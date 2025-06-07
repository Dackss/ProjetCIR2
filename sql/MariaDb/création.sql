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
                        id_region VARCHAR(10) PRIMARY KEY,
                        nom_region VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Département (
                             code_departement VARCHAR(10) PRIMARY KEY,
                             nom_departement VARCHAR(255),
                             id_region_Région VARCHAR(10),
                             FOREIGN KEY (id_region_Région) REFERENCES Région(id_region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Commune (
                         code_insee VARCHAR(10) PRIMARY KEY,
                         nom_commune VARCHAR(255),
                         population INT,
                         code_postal INT,
                         code_departement_Département VARCHAR(10),
                         FOREIGN KEY (code_departement_Département) REFERENCES Département(code_departement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE MarquePanneau (
                               id_marque INT AUTO_INCREMENT PRIMARY KEY,
                               nom_marque VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ModelePanneau (
                               id_modele INT AUTO_INCREMENT PRIMARY KEY,
                               nom_modele VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Panneau (
                         id_panneau INT AUTO_INCREMENT PRIMARY KEY,
                         modele_panneau VARCHAR(255),
                         id_marque_MarquePanneau INT,
                         id_modele_ModelePanneau INT,
                         FOREIGN KEY (id_marque_MarquePanneau) REFERENCES MarquePanneau(id_marque),
                         FOREIGN KEY (id_modele_ModelePanneau) REFERENCES ModelePanneau(id_modele)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Installateur (
                              id_installateur INT AUTO_INCREMENT PRIMARY KEY,
                              nom_installateur VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE MarqueOnduleur (
                                id_marque INT AUTO_INCREMENT PRIMARY KEY,
                                nom_marque VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ModeleOnduleur (
                                id_modele INT AUTO_INCREMENT PRIMARY KEY,
                                nom_modele VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Onduleur (
                          id_onduleur INT AUTO_INCREMENT PRIMARY KEY,
                          modele_onduleur VARCHAR(255),
                          id_marque_MarqueOnduleur INT,
                          id_modele_ModeleOnduleur INT,
                          FOREIGN KEY (id_marque_MarqueOnduleur) REFERENCES MarqueOnduleur(id_marque),
                          FOREIGN KEY (id_modele_ModeleOnduleur) REFERENCES ModeleOnduleur(id_modele)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Installation (
                              id_installation INT AUTO_INCREMENT PRIMARY KEY,
                              date_installation DATE,
                              nb_panneaux INT,
                              nb_onduleur INT,
                              surface DECIMAL(6,2),
                              puissance DECIMAL(6,2),
                              latitude DECIMAL(9,6),
                              longitude DECIMAL(9,6),
                              pente DECIMAL(4,1),
                              pente_optimum DECIMAL(4,1),
                              orientation SMALLINT,
                              orientation_optimum SMALLINT,
                              production_pvgis DECIMAL(8,2),
                              id_onduleur_Onduleur INT,
                              id_installateur_Installateur INT,
                              id_panneau_Panneau INT,
                              code_insee_Commune VARCHAR(10),
                              FOREIGN KEY (id_onduleur_Onduleur) REFERENCES Onduleur(id_onduleur),
                              FOREIGN KEY (id_installateur_Installateur) REFERENCES Installateur(id_installateur),
                              FOREIGN KEY (id_panneau_Panneau) REFERENCES Panneau(id_panneau),
                              FOREIGN KEY (code_insee_Commune) REFERENCES Commune(code_insee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE Admin (
                       id_admin INT AUTO_INCREMENT PRIMARY KEY,
                       identifiant VARCHAR(100) NOT NULL UNIQUE,
                       mot_de_passe VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



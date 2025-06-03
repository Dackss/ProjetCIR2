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
                        id_region TEXT PRIMARY KEY,
                        nom_region TEXT
);

CREATE TABLE Departement (
                             code_departement TEXT PRIMARY KEY,
                             nom_departement TEXT,
                             id_region TEXT REFERENCES Region(id_region)
);

CREATE TABLE Commune (
                         code_insee TEXT PRIMARY KEY,
                         nom_commune TEXT,
                         population INTEGER,
                         code_departement TEXT REFERENCES Departement(code_departement)
);

CREATE TABLE CodePostal (
                            code_postal INTEGER PRIMARY KEY
);

CREATE TABLE CommuneCodePostal (
                                   code_insee TEXT,
                                   code_postal INTEGER,
                                   PRIMARY KEY (code_insee, code_postal),
                                   FOREIGN KEY (code_insee) REFERENCES Commune(code_insee),
                                   FOREIGN KEY (code_postal) REFERENCES CodePostal(code_postal)
);

CREATE TABLE MarquePanneau (
                               id_marque SERIAL PRIMARY KEY,
                               nom_marque TEXT
);

CREATE TABLE ModelePanneau (
                               id_modele SERIAL PRIMARY KEY,
                               nom_modele TEXT
);

CREATE TABLE Panneau (
                         id_panneau SERIAL PRIMARY KEY,
                         id_marque INTEGER REFERENCES MarquePanneau(id_marque),
                         id_modele INTEGER REFERENCES ModelePanneau(id_modele)
);

CREATE TABLE MarqueOnduleur (
                                id_marque SERIAL PRIMARY KEY,
                                nom_marque TEXT
);

CREATE TABLE ModeleOnduleur (
                                id_modele SERIAL PRIMARY KEY,
                                nom_modele TEXT
);

CREATE TABLE Onduleur (
                          id_onduleur SERIAL PRIMARY KEY,
                          id_marque INTEGER REFERENCES MarqueOnduleur(id_marque),
                          id_modele INTEGER REFERENCES ModeleOnduleur(id_modele)
);

CREATE TABLE Installateur (
                              id_installateur SERIAL PRIMARY KEY,
                              nom_installateur TEXT
);

CREATE TABLE Installation (
                              id_installation SERIAL PRIMARY KEY,
                              date_installation DATE,
                              nb_panneaux INTEGER,
                              surface DECIMAL(6,2),
                              puissance DECIMAL(6,2),
                              latitude DECIMAL(9,6),
                              longitude DECIMAL(9,6),
                              pente DECIMAL(4,1),
                              pente_optimum DECIMAL(4,1),
                              orientation SMALLINT,
                              orientation_optimum SMALLINT,
                              production_pvgis DECIMAL(8,2),
                              id_installateur INTEGER REFERENCES Installateur(id_installateur),
                              code_insee TEXT REFERENCES Commune(code_insee)
);

CREATE TABLE InstallationOnduleur (
                                      id_installation INTEGER,
                                      id_onduleur INTEGER,
                                      quantite_onduleurs SMALLINT,
                                      PRIMARY KEY (id_installation, id_onduleur),
                                      FOREIGN KEY (id_installation) REFERENCES Installation(id_installation),
                                      FOREIGN KEY (id_onduleur) REFERENCES Onduleur(id_onduleur)
);

CREATE TABLE InstallationPanneau (
                                     id_installation INTEGER,
                                     id_panneau INTEGER,
                                     quantite_panneaux SMALLINT,
                                     PRIMARY KEY (id_installation, id_panneau),
                                     FOREIGN KEY (id_installation) REFERENCES Installation(id_installation),
                                     FOREIGN KEY (id_panneau) REFERENCES Panneau(id_panneau)
);

DROP TABLE IF EXISTS
    InstallationPanneau,
    InstallationOnduleur,
    Installation,
    Panneau,
    Onduleur,
    Installateur,
    Marque,
    CommuneCodePostal,
    CodePostal,
    Commune,
    Departement,
    Region
    CASCADE;


CREATE TABLE Region (
                        id_region TEXT PRIMARY KEY,
                        nom_region VARCHAR(100)
);

CREATE TABLE Departement (
                             code_departement TEXT PRIMARY KEY,
                             nom_departement VARCHAR(100),
                             id_region TEXT,
                             FOREIGN KEY (id_region) REFERENCES Region(id_region)
);

CREATE TABLE Commune (
                         code_insee TEXT PRIMARY KEY,
                         nom_commune VARCHAR(100),
                         population INTEGER,
                         code_departement TEXT,
                         FOREIGN KEY (code_departement) REFERENCES Departement(code_departement)
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

CREATE TABLE Marque (
                        id_marque SERIAL PRIMARY KEY,
                        nom_marque VARCHAR(100)
);

CREATE TABLE Panneau (
                         id_panneau SERIAL PRIMARY KEY,
                         modele_panneau VARCHAR(100),
                         id_marque INT,
                         FOREIGN KEY (id_marque) REFERENCES Marque(id_marque)
);

CREATE TABLE Onduleur (
                          id_onduleur SERIAL PRIMARY KEY,
                          modele_onduleur VARCHAR(100),
                          id_marque INT,
                          FOREIGN KEY (id_marque) REFERENCES Marque(id_marque)
);

CREATE TABLE Installateur (
                              id_installateur SERIAL PRIMARY KEY,
                              nom_installateur VARCHAR(100)
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
                              id_installateur INT,
                              code_insee TEXT,
                              FOREIGN KEY (id_installateur) REFERENCES Installateur(id_installateur),
                              FOREIGN KEY (code_insee) REFERENCES Commune(code_insee)
);

CREATE TABLE InstallationOnduleur (
                                      id_installation INT,
                                      id_onduleur INT,
                                      quantite_onduleurs SMALLINT,
                                      PRIMARY KEY (id_installation, id_onduleur),
                                      FOREIGN KEY (id_installation) REFERENCES Installation(id_installation),
                                      FOREIGN KEY (id_onduleur) REFERENCES Onduleur(id_onduleur)
);

CREATE TABLE InstallationPanneau (
                                     id_installation INT,
                                     id_panneau INT,
                                     quantite_panneaux SMALLINT,
                                     PRIMARY KEY (id_installation, id_panneau),
                                     FOREIGN KEY (id_installation) REFERENCES Installation(id_installation),
                                     FOREIGN KEY (id_panneau) REFERENCES Panneau(id_panneau)
);

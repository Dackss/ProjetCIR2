DROP TABLE IF EXISTS Installation CASCADE;
DROP TABLE IF EXISTS Commune CASCADE;
DROP TABLE IF EXISTS Département CASCADE;
DROP TABLE IF EXISTS Région CASCADE;
DROP TABLE IF EXISTS MarquePanneau CASCADE;
DROP TABLE IF EXISTS ModelePanneau CASCADE;
DROP TABLE IF EXISTS Panneau CASCADE;
DROP TABLE IF EXISTS Installateur CASCADE;
DROP TABLE IF EXISTS ModeleOnduleur CASCADE;
DROP TABLE IF EXISTS MarqueOnduleur CASCADE;
DROP TABLE IF EXISTS Onduleur CASCADE;

CREATE TABLE Région (
                        id_region TEXT PRIMARY KEY,
                        nom_region TEXT
);

CREATE TABLE Département (
                             code_departement TEXT PRIMARY KEY,
                             nom_departement TEXT,
                             id_region_Région TEXT REFERENCES Région(id_region)
);

CREATE TABLE Commune (
                         code_insee TEXT PRIMARY KEY,
                         nom_commune TEXT,
                         population INTEGER,
                         code_postal TEXT,
                         code_departement_Département TEXT REFERENCES Département(code_departement)
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
                         id_marque_MarquePanneau INTEGER REFERENCES MarquePanneau(id_marque),
                         id_modele_ModelePanneau INTEGER REFERENCES ModelePanneau(id_modele)
);

CREATE TABLE Installateur (
                              id_installateur SERIAL PRIMARY KEY,
                              nom_installateur TEXT
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
                          id_marque_MarqueOnduleur INTEGER REFERENCES MarqueOnduleur(id_marque),
                          id_modele_ModeleOnduleur INTEGER REFERENCES ModeleOnduleur(id_modele)
);

CREATE TABLE Installation (
                              id_installation SERIAL PRIMARY KEY,
                              date_installation DATE,
                              nb_onduleur INTEGER,
                              nb_panneaux INTEGER,
                              surface NUMERIC(6,2),
                              puissance NUMERIC(6,2),
                              latitude NUMERIC(9,6),
                              longitude NUMERIC(9,6),
                              pente NUMERIC(4,1),
                              pente_optimum NUMERIC(4,1),
                              orientation SMALLINT,
                              orientation_optimum SMALLINT,
                              production_pvgis NUMERIC(8,2),
                              id_onduleur_Onduleur INTEGER REFERENCES Onduleur(id_onduleur),
                              id_installateur_Installateur INTEGER REFERENCES Installateur(id_installateur),
                              id_panneau_Panneau INTEGER REFERENCES Panneau(id_panneau),
                              code_insee_Commune TEXT REFERENCES Commune(code_insee)
);

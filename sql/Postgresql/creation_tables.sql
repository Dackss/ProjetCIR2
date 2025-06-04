DROP TABLE IF EXISTS Installation CASCADE;
DROP TABLE IF EXISTS Commune CASCADE;
DROP TABLE IF EXISTS Département CASCADE;
DROP TABLE IF EXISTS Région CASCADE;
DROP TABLE IF EXISTS MarquePanneau CASCADE;
DROP TABLE IF EXISTS Panneau CASCADE;
DROP TABLE IF EXISTS Installateur CASCADE;
DROP TABLE IF EXISTS Onduleur CASCADE;
DROP TABLE IF EXISTS ModeleOnduleur CASCADE;
DROP TABLE IF EXISTS MarqueOnduleur CASCADE;
DROP TABLE IF EXISTS ModelePanneau CASCADE;

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
                         population INT,
                         code_postal INT,
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
                         modele_panneau TEXT,
                         id_marque_MarquePanneau INT REFERENCES MarquePanneau(id_marque),
                         id_modele_ModelePanneau INT REFERENCES ModelePanneau(id_modele)
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
                          modele_onduleur TEXT,
                          id_marque_MarqueOnduleur INT REFERENCES MarqueOnduleur(id_marque),
                          id_modele_ModeleOnduleur INT REFERENCES ModeleOnduleur(id_modele)
);

CREATE TABLE Installation (
                              id_installation SERIAL PRIMARY KEY,
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
                              quantite_onduleurs_utilise_onduleur SMALLINT,
                              id_onduleur_Onduleur INT REFERENCES Onduleur(id_onduleur),
                              id_installateur_Installateur INT REFERENCES Installateur(id_installateur),
                              quantite_panneaux_utilise_panneau SMALLINT,
                              id_panneau_Panneau INT REFERENCES Panneau(id_panneau),
                              code_insee_Commune TEXT REFERENCES Commune(code_insee)
);

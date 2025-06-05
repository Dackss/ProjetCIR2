import pandas as pd
import mysql.connector

# Connexion MariaDB
conn = mysql.connector.connect(
    host="localhost",
    user="projetCIR2",
    password="isen",
    database="projetCIR2"
)
cur = conn.cursor()

# Lecture CSV
df = pd.read_csv("data_clean.csv")
df = df.where(pd.notnull(df), None)

# Fonction safe pour float
def safe_float(val, maxval=9999.99):
    try:
        f = float(val)
        return f if abs(f) <= maxval else None
    except:
        return None

# Fonctions d'insertion ou récupération d'ID
def get_or_create(table, fields, values):
    placeholders = ' AND '.join(f"{f}=%s" for f in fields)
    cur.execute(f"SELECT id_{table} FROM {table} WHERE {placeholders}", values)
    row = cur.fetchone()
    if row:
        return row[0]
    insert_fields = ', '.join(fields)
    insert_values = ', '.join(['%s'] * len(fields))
    cur.execute(f"INSERT INTO {table} ({insert_fields}) VALUES ({insert_values})", values)
    return cur.lastrowid

# Insertion dans les tables liées
for _, row in df.iterrows():
    id_installateur = get_or_create("installateur", ["nom"], [row["installateur"] or "non renseigné"])
    id_panneau = get_or_create("panneau", ["marque", "modele"], [row["panneaux_marque"], row["panneaux_modele"]])
    id_onduleur = get_or_create("onduleur", ["marque", "modele"], [row["onduleur_marque"], row["onduleur_modele"]])
    id_commune = get_or_create("commune",
        ["code_postal", "code_postal_suffix", "nom_commune", "localite", "niveau1", "niveau2", "niveau3", "niveau4", "pays"],
        [row["postal_code"], row["postal_code_suffix"], row["postal_town"], row["locality"],
         row["administrative_area_level_1"], row["administrative_area_level_2"],
         row["administrative_area_level_3"], row["administrative_area_level_4"],
         row["country"]])

    try:
        orientation = int(row["orientation"])
    except:
        orientation = None

    try:
        orientation_optimum = int(row["orientation_optimum"])
    except:
        orientation_optimum = None

    surface = safe_float(row["surface"])
    puissance_crete = safe_float(row["puissance_crete"])
    pente = safe_float(row["pente"], maxval=99.9)
    pente_optimum = safe_float(row["pente_optimum"], maxval=99.9)
    production_pvgis = safe_float(row["production_pvgis"], maxval=999999.99)

    # Insertion finale dans installation
    cur.execute("""
        INSERT INTO installation (
            id_installation, iddoc, mois_installation, an_installation,
            nb_panneaux, id_panneau, nb_onduleur, id_onduleur,
            puissance_crete, surface, pente, pente_optimum,
            orientation, orientation_optimum, id_installateur,
            production_pvgis, lat, lon, id_commune, political
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """, (
        row["id"], row["iddoc"], row["mois_installation"], row["an_installation"],
        row["nb_panneaux"], id_panneau, row["nb_onduleur"], id_onduleur,
        puissance_crete, surface, pente, pente_optimum,
        orientation, orientation_optimum, id_installateur,
        production_pvgis, row["lat"], row["lon"], id_commune, row["political"]
    ))

conn.commit()
cur.close()
conn.close()

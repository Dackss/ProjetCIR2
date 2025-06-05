import pandas as pd
import mysql.connector
from datetime import datetime

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

def safe_float(val, maxval=9999.99):
    try:
        f = float(val)
        return f if abs(f) <= maxval else None
    except:
        return None

def get_or_create(table, fields, values, id_field=None):
    if id_field is None:
        id_field = f"id_{table}"
    placeholders = ' AND '.join(f"{f}=%s" for f in fields)
    cur.execute(f"SELECT {id_field} FROM {table} WHERE {placeholders}", values)
    row = cur.fetchone()
    if row:
        return row[0]
    insert_fields = ', '.join(fields)
    insert_values = ', '.join(['%s'] * len(fields))
    cur.execute(f"INSERT INTO {table} ({insert_fields}) VALUES ({insert_values})", values)
    return cur.lastrowid

# Boucle principale d’insertion
for _, row in df.iterrows():
    postal_code = row["postal_code"]
    code_insee = str(int(postal_code)) if pd.notnull(postal_code) else None
    departement_code = code_insee[:2] if code_insee else None
    region_id = f"R{departement_code[:1]}" if departement_code else None

    # sécurité : on ne poursuit que si les codes sont valides
    if not (code_insee and departement_code and region_id):
        continue

    # s'assurer que la région existe
    cur.execute("SELECT id_region FROM Région WHERE id_region = %s", (region_id,))
    if not cur.fetchone():
        cur.execute("INSERT INTO Région (id_region, nom_region) VALUES (%s, %s)", (
            region_id, f"Région {region_id}"
        ))

    # s'assurer que le département existe
    cur.execute("SELECT code_departement FROM Département WHERE code_departement = %s", (departement_code,))
    if not cur.fetchone():
        cur.execute("""
            INSERT INTO Département (code_departement, nom_departement, id_region_Région)
            VALUES (%s, %s, %s)
        """, (
            departement_code, f"Département {departement_code}", region_id
        ))

    # s'assurer que la commune existe
    cur.execute("SELECT code_insee FROM Commune WHERE code_insee = %s", (code_insee,))
    if not cur.fetchone():
        cur.execute("""
            INSERT INTO Commune (
                code_insee, nom_commune, code_postal, population, code_departement_Département
            ) VALUES (%s, %s, %s, %s, %s)
        """, (
            code_insee,
            row.get("postal_town"),
            postal_code,
            None,
            departement_code
        ))

    # Marques et modèles
    id_marque_pan = get_or_create("MarquePanneau", ["nom_marque"], [row["panneaux_marque"]], "id_marque")
    id_modele_pan = get_or_create("ModelePanneau", ["nom_modele"], [row["panneaux_modele"]], "id_modele")
    id_panneau = get_or_create("Panneau",
        ["modele_panneau", "id_marque_MarquePanneau", "id_modele_ModelePanneau"],
        [row["panneaux_modele"], id_marque_pan, id_modele_pan],
        "id_panneau"
    )

    id_marque_ondu = get_or_create("MarqueOnduleur", ["nom_marque"], [row["onduleur_marque"]], "id_marque")
    id_modele_ondu = get_or_create("ModeleOnduleur", ["nom_modele"], [row["onduleur_modele"]], "id_modele")
    id_onduleur = get_or_create("Onduleur",
        ["modele_onduleur", "id_marque_MarqueOnduleur", "id_modele_ModeleOnduleur"],
        [row["onduleur_modele"], id_marque_ondu, id_modele_ondu],
        "id_onduleur"
    )

    id_installateur = get_or_create("Installateur", ["nom_installateur"], [row["installateur"] or "non renseigné"], "id_installateur")

    try:
        orientation = int(row["orientation"])
    except:
        orientation = None
    try:
        orientation_optimum = int(row["orientation_optimum"])
    except:
        orientation_optimum = None

    try:
        date_installation = datetime.strptime(f"{int(row['an_installation'])}-{int(row['mois_installation']):02d}-01", "%Y-%m-%d").date()
    except:
        date_installation = None

    surface = safe_float(row["surface"])
    puissance = safe_float(row["puissance_crete"])
    pente = safe_float(row["pente"], maxval=99.9)
    pente_optimum = safe_float(row["pente_optimum"], maxval=99.9)
    production_pvgis = safe_float(row["production_pvgis"], maxval=999999.99)

    cur.execute("""
        INSERT INTO Installation (
            date_installation, nb_panneaux, nb_onduleur, surface, puissance,
            latitude, longitude, pente, pente_optimum, orientation,
            orientation_optimum, production_pvgis,
            id_onduleur_Onduleur, id_installateur_Installateur, id_panneau_Panneau,
            code_insee_Commune
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """, (
        date_installation, row["nb_panneaux"], row["nb_onduleur"], surface, puissance,
        row["lat"], row["lon"], pente, pente_optimum, orientation,
        orientation_optimum, production_pvgis,
        id_onduleur, id_installateur, id_panneau,
        code_insee
    ))

conn.commit()
cur.close()
conn.close()

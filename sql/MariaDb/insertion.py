import pandas as pd
import mysql.connector
from datetime import datetime
import unicodedata

# Connexion à la base MariaDB/MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="projetCIR2",
    password="isen",
    database="projetCIR2"
)
cur = conn.cursor()

# Fonctions utilitaires
def normalize(s):
    if not isinstance(s, str):
        return ""
    return (
        unicodedata.normalize('NFKD', s)
        .encode('ascii', errors='ignore')
        .decode('utf-8')
        .strip()
        .upper()
        .replace("-", " ")
    )

def safe_float(val, maxval=9999.99):
    try:
        f = float(val)
        return f if abs(f) <= maxval else None
    except:
        return None

def get_or_create(table, fields, values, id_field=None):
    if id_field is None:
        id_field = f"id_{table}"
    where_clause = " AND ".join(f"{f}=%s" for f in fields)
    cur.execute(f"SELECT {id_field} FROM {table} WHERE {where_clause}", values)
    row = cur.fetchone()
    if row:
        return row[0]
    insert_fields = ", ".join(fields)
    placeholders = ", ".join(["%s"] * len(fields))
    cur.execute(
        f"INSERT INTO {table} ({insert_fields}) VALUES ({placeholders})",
        values
    )
    return cur.lastrowid

# 1. Lecture du CSV d’installations (virgules)
df_inst = pd.read_csv("data_clean.csv", dtype=str)
df_inst = df_inst.where(pd.notnull(df_inst), None)

# Conversion du code postal en entier pour la fusion
def to_int_cp(x):
    if x is None:
        return None
    try:
        return int(x)
    except:
        return None

df_inst["postal_code_int"] = df_inst["postal_code"].apply(to_int_cp)

# 2. Lecture du CSV des communes (point-virgule)
df_com = pd.read_csv("communes-france-2024-limite.csv", sep=';', dtype=str)
df_com = df_com.where(pd.notnull(df_com), None)

# Conversion du code_postal en entier
df_com["code_postal_int"] = df_com["code_postal"].apply(lambda x: int(x) if x is not None else None)

# 3. Fusion pour récupérer code_insee, nom_standard, reg_code, reg_nom, dep_code, dep_nom, population
df = df_inst.merge(
    df_com[
        [
            "code_insee",
            "nom_standard",
            "reg_code",
            "reg_nom",
            "dep_code",
            "dep_nom",
            "code_postal_int",
            "population"
        ]
    ],
    left_on="postal_code_int",
    right_on="code_postal_int",
    how="left"
)

# 4. Boucle d’insertion
seen_iddocs = set()

for i, row in df.iterrows():
    # 4.1. Récupérer code_insee
    code_insee = row.get("code_insee")
    if pd.isna(code_insee) or code_insee is None:
        continue

    # 4.2. Récupérer codes et noms région/département
    region_id  = row.get("reg_code")   # ex. "84"
    nom_region = row.get("reg_nom")    # ex. "Occitanie"
    depart_code= row.get("dep_code")   # ex. "01"
    nom_dep    = row.get("dep_nom")    # ex. "Ain"

    # 4.3. Insertion ou vérif Région
    cur.execute(
        "SELECT id_region FROM Région WHERE id_region = %s",
        (region_id,)
    )
    if not cur.fetchone():
        cur.execute(
            "INSERT INTO Région (id_region, nom_region) VALUES (%s, %s)",
            (region_id, nom_region)
        )

    # 4.4. Insertion ou vérif Département
    cur.execute(
        "SELECT code_departement FROM Département WHERE code_departement = %s",
        (depart_code,)
    )
    if not cur.fetchone():
        cur.execute(
            """
            INSERT INTO Département (
                code_departement,
                nom_departement,
                id_region_Région
            ) VALUES (%s, %s, %s)
            """,
            (depart_code, nom_dep, region_id)
        )

    # 4.5. Insertion ou vérif Commune
    cur.execute(
        "SELECT code_insee FROM Commune WHERE code_insee = %s",
        (code_insee,)
    )
    if not cur.fetchone():
        nom_commune = row.get("nom_standard") or "non renseigné"
        population  = row.get("population")
        cp_original = row.get("postal_code")  # chaîne, peut être 4 ou 5 chiffres

        cur.execute(
            """
            INSERT INTO Commune (
                code_insee,
                nom_commune,
                population,
                code_postal,
                code_departement_Département
            ) VALUES (%s, %s, %s, %s, %s)
            """,
            (
                code_insee,
                nom_commune,
                population,
                cp_original,
                depart_code
            )
        )

    # 4.6. Marques et modèles de panneaux
    marque_pan  = row.get("panneaux_marque")  or "non renseigné"
    modele_pan  = row.get("panneaux_modele")  or "non renseigné"
    id_marque_pan = get_or_create("MarquePanneau", ["nom_marque"], [marque_pan], "id_marque")
    id_modele_pan = get_or_create("ModelePanneau", ["nom_modele"], [modele_pan], "id_modele")
    id_panneau    = get_or_create(
        "Panneau",
        ["modele_panneau", "id_marque_MarquePanneau", "id_modele_ModelePanneau"],
        [modele_pan, id_marque_pan, id_modele_pan],
        "id_panneau"
    )

    # 4.7. Marques et modèles d’onduleurs
    marque_ondu  = row.get("onduleur_marque") or "non renseigné"
    modele_ondu  = row.get("onduleur_modele") or "non renseigné"
    id_marque_ondu = get_or_create("MarqueOnduleur", ["nom_marque"], [marque_ondu], "id_marque")
    id_modele_ondu = get_or_create("ModeleOnduleur", ["nom_modele"], [modele_ondu], "id_modele")
    id_onduleur    = get_or_create(
        "Onduleur",
        ["modele_onduleur", "id_marque_MarqueOnduleur", "id_modele_ModeleOnduleur"],
        [modele_ondu, id_marque_ondu, id_modele_ondu],
        "id_onduleur"
    )

    # 4.8. Installateur
    nom_inst = row.get("installateur") or "non renseigné"
    id_installateur = get_or_create("Installateur", ["nom_installateur"], [nom_inst], "id_installateur")

    # 4.9. Champs numériques et date
    try:
        orientation     = int(row.get("orientation"))
    except:
        orientation = None
    try:
        orientation_opt = int(row.get("orientation_optimum"))
    except:
        orientation_opt = None

    try:
        an   = int(row.get("an_installation"))
        mois = int(row.get("mois_installation"))
        date_install = datetime.strptime(f"{an}-{mois:02d}-01", "%Y-%m-%d").date()
    except:
        date_install = None

    surface          = safe_float(row.get("surface"))
    puissance        = safe_float(row.get("puissance_crete"))
    pente            = safe_float(row.get("pente"), maxval=99.9)
    pente_opt        = safe_float(row.get("pente_optimum"), maxval=99.9)
    production_pvgis = safe_float(row.get("production_pvgis"), maxval=999999.99)

    # 4.10. Insertion dans la table Installation
    cur.execute(
        """
        INSERT INTO Installation (
            date_installation,
            nb_panneaux,
            nb_onduleur,
            surface,
            puissance,
            latitude,
            longitude,
            pente,
            pente_optimum,
            orientation,
            orientation_optimum,
            production_pvgis,
            id_onduleur_Onduleur,
            id_installateur_Installateur,
            id_panneau_Panneau,
            code_insee_Commune
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """,
        (
            date_install,
            row.get("nb_panneaux"),
            row.get("nb_onduleur"),
            surface,
            puissance,
            row.get("lat"),
            row.get("lon"),
            pente,
            pente_opt,
            orientation,
            orientation_opt,
            production_pvgis,
            id_onduleur,
            id_installateur,
            id_panneau,
            code_insee
        )
    )

    # 4.11. Print unique par iddoc (avec numéro de ligne)
    iddoc = row.get("iddoc")
    if iddoc not in seen_iddocs:
        print(
            f"Ligne {i+1} – iddoc={iddoc} – code_insee={code_insee} – date_install={date_install}"
        )
        seen_iddocs.add(iddoc)

# 5. Commit et fermeture
conn.commit()
cur.close()
conn.close()

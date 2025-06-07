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

def clean(val):
    """
    Convertit NaN, 'nan', 'NaN', et pd.NA en None.
    """
    if val is None:
        return None
    if isinstance(val, float) and pd.isna(val):
        return None
    if isinstance(val, str) and val.strip().lower() == 'nan':
        return None
    return val

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

# 1. Lecture du CSV des installations (virgules)
df_inst = pd.read_csv("data_clean.csv", dtype=str)
df_inst = df_inst.where(pd.notnull(df_inst), None)

# 1.1. Conversion du code postal en entier pour la fusion
def to_int_cp(x):
    x = clean(x)
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

# 2.1. Conversion du code_postal en entier et population en entier
df_com["code_postal_int"] = df_com["code_postal"].apply(lambda x: int(x) if x is not None else None)
df_com["population_int"]   = df_com["population"].apply(lambda x: int(x) if x is not None else 0)

# 3. Préremplir les tables Région, Département et Commune avec toutes les communes
for _, row in df_com.iterrows():
    row = row.where(pd.notnull(row), None)

    code_insee   = clean(row.get("code_insee"))
    nom_commune  = clean(row.get("nom_standard")) or "non renseigné"
    population   = clean(row.get("population"))
    code_postal  = clean(row.get("code_postal"))
    depart_code  = clean(row.get("dep_code"))
    nom_dep      = clean(row.get("dep_nom"))
    region_id    = clean(row.get("reg_code"))
    nom_region   = clean(row.get("reg_nom"))

    # 3.1. Région
    if region_id is not None:
        cur.execute("SELECT id_region FROM Région WHERE id_region = %s", (region_id,))
        if not cur.fetchone():
            cur.execute(
                "INSERT INTO Région (id_region, nom_region) VALUES (%s, %s)",
                (region_id, nom_region)
            )

    # 3.2. Département
    if depart_code is not None:
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

    # 3.3. Commune
    cur.execute("SELECT code_insee FROM Commune WHERE code_insee = %s", (code_insee,))
    if not cur.fetchone():
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
                code_postal,
                depart_code
            )
        )

conn.commit()

# 4. Fusion pour associer installations à communes (1 row par CSV row)
df_merged = df_inst.merge(
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
df_merged = df_merged.where(pd.notnull(df_merged), None)

# 5. Boucle d’insertion : une ligne de BDD par ligne de CSV
for i, row in df_merged.iterrows():
    row = row.where(pd.notnull(row), None)

    # 5.1. Date d'installation
    mois = clean(row.get("mois_installation"))
    an = clean(row.get("an_installation"))
    try:
        date_install = datetime.strptime(f"{int(an)}-{int(mois):02d}-01", "%Y-%m-%d").date()
    except:
        date_install = None

    # 5.2. Comptage des panneaux et onduleurs
    nb_panneaux = clean(row.get("nb_panneaux"))
    nb_onduleur = clean(row.get("nb_onduleur"))

    # 5.3. Surface
    surface = safe_float(clean(row.get("surface")))

    # 5.4. Puissance crête (prendre la valeur du CSV, sans sommation)
    puissance_w = safe_float(clean(row.get("puissance_crete")))
    # On convertit en kW pour rester dans une plage raisonnable
    puissance_insert = round(puissance_w / 1000, 2) if puissance_w is not None else None

    # 5.5. Coordonnées et angles
    lat = clean(row.get("lat"))
    lon = clean(row.get("lon"))
    pente = safe_float(clean(row.get("pente")), maxval=99.9)
    pente_opt = safe_float(clean(row.get("pente_optimum")), maxval=99.9)
    try:
        orientation = int(clean(row.get("orientation")))
    except:
        orientation = None
    try:
        orientation_opt = int(clean(row.get("orientation_optimum")))
    except:
        orientation_opt = None

    # 5.6. Production PVGIS
    production_pvgis = safe_float(clean(row.get("production_pvgis")))

    # 5.7. Code INSEE + région/département
    code_insee = clean(row.get("code_insee"))
    depart_code = clean(row.get("dep_code"))
    region_id = clean(row.get("reg_code"))

    # Insert Région si nécessaire
    if region_id is not None:
        cur.execute("SELECT id_region FROM Région WHERE id_region = %s", (region_id,))
        if not cur.fetchone():
            cur.execute(
                "INSERT INTO Région (id_region, nom_region) VALUES (%s, %s)",
                (region_id, clean(row.get("reg_nom")))
            )

    # Insert Département si nécessaire
    if depart_code is not None:
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
                (depart_code, clean(row.get("dep_nom")), region_id)
            )

    # 5.8. Marques et modèles de panneaux
    marque_pan = clean(row.get("panneaux_marque")) or "non renseigné"
    modele_pan = clean(row.get("panneaux_modele")) or "non renseigné"
    id_marque_pan = get_or_create("MarquePanneau", ["nom_marque"], [marque_pan], "id_marque")
    id_modele_pan = get_or_create("ModelePanneau", ["nom_modele"], [modele_pan], "id_modele")
    id_panneau = get_or_create(
        "Panneau",
        ["modele_panneau", "id_marque_MarquePanneau", "id_modele_ModelePanneau"],
        [modele_pan, id_marque_pan, id_modele_pan],
        "id_panneau"
    )

    # 5.9. Marques et modèles d’onduleurs
    marque_ondu = clean(row.get("onduleur_marque")) or "non renseigné"
    modele_ondu = clean(row.get("onduleur_modele")) or "non renseigné"
    id_marque_ondu = get_or_create("MarqueOnduleur", ["nom_marque"], [marque_ondu], "id_marque")
    id_modele_ondu = get_or_create("ModeleOnduleur", ["nom_modele"], [modele_ondu], "id_modele")
    id_onduleur = get_or_create(
        "Onduleur",
        ["modele_onduleur", "id_marque_MarqueOnduleur", "id_modele_ModeleOnduleur"],
        [modele_ondu, id_marque_ondu, id_modele_ondu],
        "id_onduleur"
    )

    # 5.10. Installateur
    nom_inst = clean(row.get("installateur")) or "non renseigné"
    id_installateur = get_or_create("Installateur", ["nom_installateur"], [nom_inst], "id_installateur")

    # 5.11. Insertion dans la table Installation (1→1 CSV→BDD)
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
            nb_panneaux,
            nb_onduleur,
            surface,
            puissance_insert,
            lat,
            lon,
            pente,
            pente_opt,
            orientation,
            orientation_opt,
            production_pvgis,
            id_onduleur,
            id_installateur,
            id_panneau,
            code_insee  # None si pas de correspondance
        )
    )

    # Affichage d’une ligne de log pour suivre l’import
    print(
        f"Ligne {i+1} – iddoc={clean(row.get('iddoc'))} – code_insee={code_insee} – date_install={date_install}"
    )

# 6. Commit et fermeture
conn.commit()
cur.close()
conn.close()

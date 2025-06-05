import csv
import os
import unicodedata
from collections import OrderedDict, defaultdict

def norm(text):
    return unicodedata.normalize('NFKD', text or '').encode('ascii', 'ignore').decode('ascii').lower()


def quote(val):
    if val is None:
        return 'NULL'
    return f"'{str(val).replace("'", "''")}'" if not isinstance(val, (int, float)) else str(val)


def to_int(val, default=0):
    try:
        return int(val)
    except (ValueError, TypeError):
        return default


def to_float(val, default=0.0):
    try:
        return float(val)
    except (ValueError, TypeError):
        return default


def load_communes(path):
    regions, departments, communes = OrderedDict(), OrderedDict(), OrderedDict()
    by_postal_name, by_name = {}, defaultdict(list)
    with open(path, encoding='utf-8') as f:
        for row in csv.DictReader(f, delimiter=';'):
            reg_code, reg_name = row['reg_code'], row['reg_nom']
            regions.setdefault(reg_name, int(reg_code))
            dep_code, dep_name = row['dep_code'], row['dep_nom']
            departments.setdefault(dep_code, {'name': dep_name, 'region_id': int(reg_code)})
            code_insee = row['code_insee']
            communes[code_insee] = {
                'name': row['nom_standard'],
                'population': int(row['population'] or 0),
                'postal_code': int(row['code_postal']),
                'dep_code': dep_code,
            }
            key = (row['code_postal'], norm(row['nom_standard']))
            by_postal_name[key] = code_insee
            by_name[norm(row['nom_standard'])].append(code_insee)
    return regions, departments, communes, by_postal_name, by_name


def main():
    base_dir = os.path.dirname(__file__)
    communes_path = os.path.join(base_dir, 'communes-france-2024-limite.csv')
    data_path = os.path.join(base_dir, 'data_clean.csv')
    regions, departments, communes, by_postal_name, by_name = load_communes(communes_path)

    marque_panneau, modele_panneau, panneaux = OrderedDict(), OrderedDict(), OrderedDict()
    marque_onduleur, modele_onduleur, onduleurs = OrderedDict(), OrderedDict(), OrderedDict()
    installateurs, installations = OrderedDict(), []

    next_region_id, next_dept_id, next_custom_commune = max(regions.values(), default=0) + 1, 1000, 100000

    with open(data_path, newline='', encoding='utf-8') as f:
        for row in csv.DictReader(f):
            m_marque, m_modele = row['panneaux_marque'].strip(), row['panneaux_modele'].strip()
            if not m_marque or not m_modele:
                continue
            marque_panneau.setdefault(m_marque, len(marque_panneau) + 1)
            modele_panneau.setdefault(m_modele, len(modele_panneau) + 1)
            pann_key = (m_marque, m_modele)
            panneaux.setdefault(pann_key, len(panneaux) + 1)
            panneau_id = panneaux[pann_key]

            o_marque, o_modele = row['onduleur_marque'].strip(), row['onduleur_modele'].strip()
            if not o_marque or not o_modele:
                continue
            marque_onduleur.setdefault(o_marque, len(marque_onduleur) + 1)
            modele_onduleur.setdefault(o_modele, len(modele_onduleur) + 1)
            ond_key = (o_marque, o_modele)
            onduleurs.setdefault(ond_key, len(onduleurs) + 1)
            onduleur_id = onduleurs[ond_key]

            inst_name = row['installateur'].strip() or "Non renseigné"
            installateurs.setdefault(inst_name, len(installateurs) + 1)
            installateur_id = installateurs[inst_name]

            key = (row['postal_code'], norm(row['locality']))
            code_insee = by_postal_name.get(key) or next(
                (lst[0] for lst in by_name.get(norm(row['locality']), [])), None)
            if not code_insee:
                code_insee = str(next_custom_commune)
                next_custom_commune += 1
                reg_name, dep_name = row['administrative_area_level_1'] or 'Inconnue', row['administrative_area_level_2'] or 'Inconnue'
                reg_id = regions.setdefault(reg_name, next_region_id)
                next_region_id += 1
                dep_code = departments.setdefault(dep_name, {'name': dep_name, 'region_id': reg_id})
                next_dept_id += 1
                communes[code_insee] = {
                    'name': row['locality'],
                    'population': 0,
                    'postal_code': int(row['postal_code'] or 0),
                    'dep_code': dep_code,
                }
                by_postal_name[key] = code_insee

            installations.append({
                'date_installation': f"{row['an_installation']}-{row['mois_installation']}-01",
                'nb_panneaux': to_int(row['nb_panneaux']),
                'nb_onduleur': to_int(row['nb_onduleur']),
                'surface': to_float(row['surface']),
                'puissance': to_float(row['puissance_crete']),
                'latitude': to_float(row['lat']),
                'longitude': to_float(row['lon']),
                'pente': to_float(row['pente']),
                'pente_optimum': to_float(row['pente_optimum']),
                'orientation': to_int(row['orientation']),
                'orientation_optimum': to_int(row['orientation_optimum']),
                'production_pvgis': to_float(row['production_pvgis']),
                'onduleur_id': onduleur_id,
                'installateur_id': installateur_id,
                'panneau_id': panneau_id,
                'code_insee': code_insee,
            })

    ids_utilisés = {inst['onduleur_id'] for inst in installations}
    ids_existants = set(onduleurs.values())
    ids_manquants = ids_utilisés - ids_existants
    if ids_manquants:
        print("⛔ ERREUR : les ID onduleurs suivants sont utilisés mais pas insérés :", ids_manquants)

    with open('insert_data.sql', 'w', encoding='utf-8') as out:
        out.write('INSERT INTO Région (id_region, nom_region) VALUES\n')
        out.write(',\n'.join(f"({code}, {quote(name)})" for name, code in regions.items()) + ';\n\n')

        out.write('INSERT INTO Département (code_departement, nom_departement, id_region_Région) VALUES\n')
        out.write(',\n'.join(
            f"({quote(code)}, {quote(info['name'])}, {info['region_id']})"
            for code, info in departments.items()) + ';\n\n')

        out.write('INSERT INTO Commune (code_insee, nom_commune, population, code_postal, code_departement_Département) VALUES\n')
        out.write(',\n'.join(
            f"({quote(code)}, {quote(info['name'])}, {info['population']}, {info['postal_code']}, {quote(info['dep_code'])})"
            for code, info in communes.items()) + ';\n\n')

        out.write('INSERT INTO MarquePanneau (id_marque, nom_marque) VALUES\n')
        out.write(',\n'.join(f"({i}, {quote(name)})" for name, i in marque_panneau.items()) + ';\n\n')

        out.write('INSERT INTO ModelePanneau (id_modele, nom_modele) VALUES\n')
        out.write(',\n'.join(f"({i}, {quote(name)})" for name, i in modele_panneau.items()) + ';\n\n')

        out.write('INSERT INTO Panneau (id_panneau, id_marque_MarquePanneau, id_modele_ModelePanneau) VALUES\n')
        out.write(',\n'.join(
            f"({pid}, {marque_panneau[pann_key[0]]}, {modele_panneau[pann_key[1]]})"
            for pann_key, pid in panneaux.items()) + ';\n\n')

        out.write('INSERT INTO Installateur (id_installateur, nom_installateur) VALUES\n')
        out.write(',\n'.join(f"({i}, {quote(name)})" for name, i in installateurs.items()) + ';\n\n')

        out.write('INSERT INTO MarqueOnduleur (id_marque, nom_marque) VALUES\n')
        out.write(',\n'.join(f"({i}, {quote(name)})" for name, i in marque_onduleur.items() if name.strip()) + ';\n\n')

        out.write('INSERT INTO ModeleOnduleur (id_modele, nom_modele) VALUES\n')
        out.write(',\n'.join(f"({i}, {quote(name)})" for name, i in modele_onduleur.items() if name.strip()) + ';\n\n')

        out.write('INSERT INTO Onduleur (id_onduleur, id_marque_MarqueOnduleur, id_modele_ModeleOnduleur) VALUES\n')
        out.write(',\n'.join(
            f"({oid}, {marque_onduleur[ond_key[0]]}, {modele_onduleur[ond_key[1]]})"
            for ond_key, oid in onduleurs.items()) + ';\n\n')

        out.write('INSERT INTO Installation (date_installation, nb_panneaux, nb_onduleur, surface, puissance, latitude, longitude, pente, pente_optimum, orientation, orientation_optimum, production_pvgis, id_onduleur_Onduleur, id_installateur_Installateur, id_panneau_Panneau, code_insee_Commune) VALUES\n')
        out.write(',\n'.join(
            f"({quote(inst['date_installation'])}, {inst['nb_panneaux']}, {inst['nb_onduleur']}, {inst['surface']}, {inst['puissance']}, {inst['latitude']}, {inst['longitude']}, {inst['pente']}, {inst['pente_optimum']}, {inst['orientation']}, {inst['orientation_optimum']}, {inst['production_pvgis']}, {inst['onduleur_id']}, {inst['installateur_id']}, {inst['panneau_id']}, {quote(inst['code_insee'])})"
            for inst in installations) + ';\n')

    ids_manquants = ids_utilisés - set(onduleurs.values())
    if ids_manquants:
        print("⛔ ERREUR : ces onduleur_id sont utilisés mais pas insérés dans la table Onduleur :", ids_manquants)
    else:
        print("✅ Tous les onduleur_id utilisés sont bien insérés.")


if __name__ == '__main__':
    main()
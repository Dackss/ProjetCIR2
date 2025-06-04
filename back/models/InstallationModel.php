<?php
class InstallationModel {
	private $pdo;

	public function __construct() {
		$this->pdo = Database::getInstance();
	}

	public function getAll($filtres = []) {
		$sql = "
            SELECT 
                i.id_installation,
                TO_CHAR(i.date_installation, 'MM/YYYY') AS date_installation,
                i.nb_panneaux,
                i.nb_onduleur,
                i.surface,
                i.puissance,
                i.latitude,
                i.longitude,
                i.pente,
                i.pente_optimum,
                i.orientation,
                i.orientation_optimum,
                i.production_pvgis,
                c.nom_commune,
                c.code_postal
            FROM Installation i
            JOIN Commune c ON i.code_insee_Commune = c.code_insee
            JOIN Département d ON c.code_departement_Département = d.code_departement
            JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
            JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
            JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
            JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
            WHERE 1=1
        ";

		$params = [];

		if (!empty($filtres['onduleur'])) {
			$sql .= " AND mo.nom_marque = :onduleur";
			$params[':onduleur'] = $filtres['onduleur'];
		}
		if (!empty($filtres['panneau'])) {
			$sql .= " AND mp.nom_marque = :panneau";
			$params[':panneau'] = $filtres['panneau'];
		}
		if (!empty($filtres['departement'])) {
			$sql .= " AND d.code_departement = :departement";
			$params[':departement'] = $filtres['departement'];
		}

		$sql .= " LIMIT 100";

		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getOne($id) {
		$sql = "SELECT * FROM Installation WHERE id_installation = :id";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute([':id' => $id]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function create($data) {
		$sql = "INSERT INTO Installation (
                    date_installation, nb_panneaux, nb_onduleur, surface, puissance,
                    latitude, longitude, pente, pente_optimum, orientation,
                    orientation_optimum, production_pvgis,
                    id_onduleur_Onduleur, id_installateur_Installateur, id_panneau_Panneau,
                    code_insee_Commune
                )
                VALUES (
                    :date_installation, :nb_panneaux, :nb_onduleur, :surface, :puissance,
                    :latitude, :longitude, :pente, :pente_optimum, :orientation,
                    :orientation_optimum, :production_pvgis,
                    :id_onduleur, :id_installateur, :id_panneau, :code_insee
                )";

		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':date_installation' => $data['date_installation'],
			':nb_panneaux' => $data['nb_panneaux'],
			':nb_onduleur' => $data['nb_onduleur'],
			':surface' => $data['surface'],
			':puissance' => $data['puissance'],
			':latitude' => $data['latitude'],
			':longitude' => $data['longitude'],
			':pente' => $data['pente'],
			':pente_optimum' => $data['pente_optimum'],
			':orientation' => $data['orientation'],
			':orientation_optimum' => $data['orientation_optimum'],
			':production_pvgis' => $data['production_pvgis'],
			':id_onduleur' => $data['id_onduleur_Onduleur'],
			':id_installateur' => $data['id_installateur_Installateur'],
			':id_panneau' => $data['id_panneau_Panneau'],
			':code_insee' => $data['code_insee_Commune']
		]);
	}

	public function update($id, $data) {
		$sql = "UPDATE Installation SET
                    nb_panneaux = :nb_panneaux,
                    nb_onduleur = :nb_onduleur,
                    surface = :surface,
                    puissance = :puissance
                WHERE id_installation = :id";

		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':nb_panneaux' => $data['nb_panneaux'],
			':nb_onduleur' => $data['nb_onduleur'],
			':surface' => $data['surface'],
			':puissance' => $data['puissance'],
			':id' => $id
		]);
	}

	public function delete($id) {
		$sql = "DELETE FROM Installation WHERE id_installation = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([':id' => $id]);
	}

	public function getStatistiquesAccueil() {
		$s = [];

		$s['nb_installations'] = $this->pdo->query("SELECT COUNT(*) FROM Installation")->fetchColumn();
		$s['nb_par_annee'] = $this->pdo->query("SELECT COUNT(DISTINCT EXTRACT(YEAR FROM date_installation)) FROM Installation")->fetchColumn();
		$s['nb_par_region'] = $this->pdo->query("
            SELECT COUNT(DISTINCT d.id_region_Région)
            FROM Installation i
            JOIN Commune c ON i.code_insee_Commune = c.code_insee
            JOIN Département d ON c.code_departement_Département = d.code_departement
        ")->fetchColumn();
		$s['nb_par_annee_region'] = $this->pdo->query("
            SELECT COUNT(*) FROM (
                SELECT DISTINCT EXTRACT(YEAR FROM i.date_installation), d.id_region_Région
                FROM Installation i
                JOIN Commune c ON i.code_insee_Commune = c.code_insee
                JOIN Département d ON c.code_departement_Département = d.code_departement
            ) AS sub
        ")->fetchColumn();
		$s['nb_installateurs'] = $this->pdo->query("SELECT COUNT(*) FROM Installateur")->fetchColumn();
		$s['nb_onduleurs'] = $this->pdo->query("SELECT COUNT(*) FROM MarqueOnduleur")->fetchColumn();
		$s['nb_panneaux'] = $this->pdo->query("SELECT COUNT(*) FROM MarquePanneau")->fetchColumn();

		return $s;
	}

    public function getAllPaginated($filtres, $page, $parPage) {
        $offset = ($page - 1) * $parPage;

        $sql = "
        SELECT 
            i.id_installation AS id,
            i.surface,
            i.pente,
            i.orientation_optimum AS orientation
        FROM Installation i
        JOIN Commune c ON i.code_insee_Commune = c.code_insee
        JOIN Département d ON c.code_departement_Département = d.code_departement
        JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
        JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
        JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
        JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
        WHERE 1=1
    ";

        $params = [];

        if (!empty($filtres['onduleur'])) {
            $sql .= " AND mo.nom_marque = :onduleur";
            $params[':onduleur'] = $filtres['onduleur'];
        }
        if (!empty($filtres['panneau'])) {
            $sql .= " AND mp.nom_marque = :panneau";
            $params[':panneau'] = $filtres['panneau'];
        }
        if (!empty($filtres['departement'])) {
            $sql .= " AND d.code_departement = :departement";
            $params[':departement'] = $filtres['departement'];
        }

        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $installations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compte total avec les mêmes filtres
        $countSql = "
        SELECT COUNT(*) FROM Installation i
        JOIN Commune c ON i.code_insee_Commune = c.code_insee
        JOIN Département d ON c.code_departement_Département = d.code_departement
        JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
        JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
        JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
        JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
        WHERE 1=1
    " . str_replace("SELECT", "", strstr($sql, "AND", false)); // réutilise la même clause WHERE

        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val);
        }
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        return [
            'installations' => $installations,
            'total' => $total
        ];
    }

}





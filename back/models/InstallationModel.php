<?php
class InstallationModel {
	private $pdo;

	public function __construct() {
		$this->pdo = Database::getInstance();
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

	public function getInstallations($filtres) {
		$sql = "
            SELECT 
                TO_CHAR(i.date_installation, 'MM/YYYY') AS date_installation,
                i.nb_panneaux,
                i.surface,
                i.puissance,
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
		$sql = "INSERT INTO Installation (date_installation, nb_panneaux, surface, puissance, latitude, longitude, ...)
            VALUES (:date_installation, :nb_panneaux, :surface, :puissance, :latitude, :longitude, ...)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':date_installation' => $data['date_installation'],
			':nb_panneaux' => $data['nb_panneaux'],
			// etc.
		]);
	}

	public function update($id, $data) {
		$sql = "UPDATE Installation SET nb_panneaux = :nb_panneaux, puissance = :puissance WHERE id_installation = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			':nb_panneaux' => $data['nb_panneaux'],
			':puissance' => $data['puissance'],
			':id' => $id
		]);
	}

	public function delete($id) {
		$sql = "DELETE FROM Installation WHERE id_installation = :id";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([':id' => $id]);
	}
}

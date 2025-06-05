<?php
require_once __DIR__ . '/../core/Database.php';

class InstallationModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function getAll($filtres = []) {
        $sql = "
            SELECT 
                i.id_installation,
                CONCAT(i.mois_installation, '/', i.an_installation) AS date_installation,
                i.nb_panneaux,
                i.nb_onduleur,
                i.surface,
                i.puissance_crete,
                i.lat AS latitude,
                i.lon AS longitude,
                i.pente,
                i.pente_optimum,
                i.orientation,
                i.orientation_optimum,
                i.production_pvgis,
                c.nom_commune,
                c.code_postal
            FROM installation i
            JOIN commune c ON i.id_commune = c.id_commune
            JOIN onduleur o ON i.id_onduleur = o.id_onduleur
            JOIN panneau p ON i.id_panneau = p.id_panneau
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtres['onduleur'])) {
            $sql .= " AND o.marque = :onduleur";
            $params[':onduleur'] = $filtres['onduleur'];
        }
        if (!empty($filtres['panneau'])) {
            $sql .= " AND p.marque = :panneau";
            $params[':panneau'] = $filtres['panneau'];
        }
        if (!empty($filtres['departement'])) {
            $sql .= " AND c.code_postal LIKE :departement";
            $params[':departement'] = $filtres['departement'] . '%';
        }

        $sql .= " LIMIT 100";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne($id) {
        $sql = "SELECT * FROM installation WHERE id_installation = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO installation (
                    iddoc, mois_installation, an_installation,
                    nb_panneaux, id_panneau, nb_onduleur, id_onduleur,
                    puissance_crete, surface, pente, pente_optimum,
                    orientation, orientation_optimum, id_installateur,
                    production_pvgis, lat, lon, id_commune, political, id_installation
                ) VALUES (
                    :iddoc, :mois_installation, :an_installation,
                    :nb_panneaux, :id_panneau, :nb_onduleur, :id_onduleur,
                    :puissance_crete, :surface, :pente, :pente_optimum,
                    :orientation, :orientation_optimum, :id_installateur,
                    :production_pvgis, :lat, :lon, :id_commune, :political, :id_installation
                )";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $sql = "UPDATE installation SET
                    nb_panneaux = :nb_panneaux,
                    nb_onduleur = :nb_onduleur,
                    surface = :surface,
                    puissance_crete = :puissance
                WHERE id_installation = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nb_panneaux' => $data['nb_panneaux'],
            ':nb_onduleur' => $data['nb_onduleur'],
            ':surface' => $data['surface'],
            ':puissance' => $data['puissance_crete'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM installation WHERE id_installation = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getStatistiquesAccueil() {
        $s = [];

        $s['nb_installations'] = $this->pdo->query("SELECT COUNT(*) FROM installation")->fetchColumn();

        $s['nb_par_annee'] = $this->pdo->query("
            SELECT an_installation AS annee, COUNT(*) AS total
            FROM installation
            GROUP BY an_installation
            ORDER BY an_installation
        ")->fetchAll(PDO::FETCH_ASSOC);

        $s['nb_par_region'] = $this->pdo->query("
            SELECT c.niveau1 AS region, COUNT(*) AS total
            FROM installation i
            JOIN commune c ON i.id_commune = c.id_commune
            GROUP BY c.niveau1
        ")->fetchAll(PDO::FETCH_ASSOC);

        $s['nb_par_annee_region'] = $this->pdo->query("
            SELECT an_installation AS annee, c.niveau1 AS region, COUNT(*) AS total
            FROM installation i
            JOIN commune c ON i.id_commune = c.id_commune
            GROUP BY an_installation, c.niveau1
            ORDER BY an_installation, c.niveau1
        ")->fetchAll(PDO::FETCH_ASSOC);

        $s['nb_installateurs'] = $this->pdo->query("SELECT COUNT(*) FROM installateur")->fetchColumn();
        $s['nb_onduleurs'] = $this->pdo->query("SELECT COUNT(DISTINCT marque) FROM onduleur")->fetchColumn();
        $s['nb_panneaux'] = $this->pdo->query("SELECT COUNT(DISTINCT marque) FROM panneau")->fetchColumn();

        return $s;
    }
}

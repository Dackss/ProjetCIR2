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
                DATE_FORMAT(i.date_installation, '%m/%Y') AS date_installation,
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
            FROM `Installation` i
            JOIN `Commune` c ON i.code_insee_commune = c.code_insee
            JOIN `Département` d ON c.code_departement_departement = d.code_departement
            JOIN `Onduleur` o ON i.id_onduleur_onduleur = o.id_onduleur
            JOIN `MarqueOnduleur` mo ON o.id_marque_marqueonduleur = mo.id_marque
            JOIN `Panneau` p ON i.id_panneau_panneau = p.id_panneau
            JOIN `MarquePanneau` mp ON p.id_marque_marquepanneau = mp.id_marque
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
        $sql = "SELECT * FROM `Installation` WHERE id_installation = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO `Installation` (
                    date_installation, nb_panneaux, nb_onduleur, surface, puissance,
                    latitude, longitude, pente, pente_optimum, orientation,
                    orientation_optimum, production_pvgis,
                    id_onduleur_onduleur, id_installateur_installateur, id_panneau_panneau,
                    code_insee_commune
                ) VALUES (
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
        $sql = "UPDATE `Installation` SET
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
        $sql = "DELETE FROM `Installation` WHERE id_installation = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getStatistiquesAccueil() {
        return [
            'nb_installations' => $this->pdo->query("SELECT COUNT(*) FROM `Installation`")->fetchColumn(),

            'nb_par_annee' => $this->pdo->query("
            SELECT COUNT(DISTINCT YEAR(date_installation)) FROM `Installation`
        ")->fetchColumn(),

            'nb_par_region' => $this->pdo->query("
            SELECT COUNT(DISTINCT d.id_region_Région)
            FROM `Installation` i
            JOIN `Commune` c ON i.code_insee_commune = c.code_insee
            JOIN `Département` d ON c.code_departement_Département = d.code_departement
        ")->fetchColumn(),

            'nb_par_annee_region' => $this->pdo->query("
            SELECT COUNT(*) FROM (
                SELECT DISTINCT YEAR(i.date_installation), d.id_region_Région
                FROM `Installation` i
                JOIN `Commune` c ON i.code_insee_commune = c.code_insee
                JOIN `Département` d ON c.code_departement_Département = d.code_departement
            ) AS sub
        ")->fetchColumn(),

            'nb_installateurs' => $this->pdo->query("SELECT COUNT(*) FROM `Installateur`")->fetchColumn(),

            'nb_onduleurs' => $this->pdo->query("SELECT COUNT(*) FROM `MarqueOnduleur`")->fetchColumn(),

            'nb_panneaux' => $this->pdo->query("SELECT COUNT(*) FROM `MarquePanneau`")->fetchColumn()
        ];
    }


    public function getAnneesInstallation() {
        $sql = "SELECT DISTINCT YEAR(date_installation) AS annee FROM `Installation` ORDER BY annee LIMIT 20";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getDepartementsAleatoires() {
        $sql = "
        SELECT DISTINCT LEFT(code_postal, 2) AS departement
        FROM `Commune`
        WHERE code_postal IS NOT NULL
        ORDER BY RAND()
        LIMIT 20
    ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }


    public function getInstallationsParFiltre($annee, $departement) {
        $sql = "
        SELECT 
            i.id_installation,
            i.latitude,
            i.longitude,
            c.nom_commune AS localite,
            i.puissance
        FROM Installation i
        JOIN Commune c ON i.code_insee_commune = c.code_insee
        WHERE YEAR(i.date_installation) = :annee
          AND LEFT(c.code_postal, 2) = :departement
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ":annee" => $annee,
            ":departement" => $departement
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getOptionsDynamiques($annee = null, $departement = null) {
        $annees = [];
        $departements = [];

        // Années valides pour un département
        if ($departement) {
            $sql = "
            SELECT DISTINCT YEAR(i.date_installation) as annee
            FROM Installation i
            JOIN Commune c ON i.code_insee_commune = c.code_insee
            WHERE LEFT(c.code_postal, 2) = :departement
            ORDER BY annee
            LIMIT 20
        ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':departement' => $departement]);
            $annees = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // Départements valides pour une année
        if ($annee) {
            $sql = "
            SELECT DISTINCT LEFT(c.code_postal, 2) as dep
            FROM Installation i
            JOIN Commune c ON i.code_insee_commune = c.code_insee
            WHERE YEAR(i.date_installation) = :annee
              AND c.code_postal IS NOT NULL
            ORDER BY dep
            LIMIT 20
        ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':annee' => $annee]);
            $departements = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        // Cas initial : aucun filtre
        if (!$annee && !$departement) {
            $annees = $this->getAnneesInstallation();
            $departements = $this->getDepartementsAleatoires();
        }

        return [
            "annees" => $annees,
            "departements" => $departements
        ];
    }

}

<?php
class InstallationModel
{
    private $pdo;
    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }
    public function getAll($filtres = [])
    {
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
    public function getOne($id)
    {
        $sql = "SELECT * FROM `Installation` WHERE id_installation = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function create($data)
    {
        foreach ($data as $k => $v) {
            $data[$k] = ($v === '' || $v === null) ? null : $v;
        }

        $stmt = $this->pdo->prepare("
        INSERT INTO Installation (
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
        )
    ");

        $ok = $stmt->execute([
            ':date_installation' => $data['date_installation'],
            ':nb_panneaux' => (int)$data['nb_panneaux'],
            ':nb_onduleur' => (int)$data['nb_onduleur'],
            ':surface' => (float)$data['surface'],
            ':puissance' => (float)$data['puissance'],
            ':latitude' => (float)$data['latitude'],
            ':longitude' => (float)$data['longitude'],
            ':pente' => $data['pente'] !== null ? (float)$data['pente'] : null,
            ':pente_optimum' => $data['pente_optimum'] !== null ? (float)$data['pente_optimum'] : null,
            ':orientation' => $data['orientation'] !== null ? (int)$data['orientation'] : null,
            ':orientation_optimum' => $data['orientation_optimum'] !== null ? (int)$data['orientation_optimum'] : null,
            ':production_pvgis' => $data['production_pvgis'] !== null ? (float)$data['production_pvgis'] : null,
            ':id_onduleur' => (int)$data['id_onduleur_Onduleur'],
            ':id_installateur' => (int)$data['id_installateur_Installateur'],
            ':id_panneau' => (int)$data['id_panneau_Panneau'],
            ':code_insee' => $data['code_insee']
        ]);

        if (!$ok) {
            file_put_contents("/tmp/sql_error.log", print_r($stmt->errorInfo(), true));
        }

        return $ok;
    }
    public function update($id, $data)
    {
        $stmt = $this->pdo->prepare("
        UPDATE Installation SET
            date_installation = :date_installation,
            nb_panneaux = :nb_panneaux,
            nb_onduleur = :nb_onduleur,
            surface = :surface,
            puissance = :puissance,
            latitude = :latitude,
            longitude = :longitude,
            pente = :pente,
            pente_optimum = :pente_optimum,
            orientation = :orientation,
            orientation_optimum = :orientation_optimum,
            production_pvgis = :production_pvgis,
            id_onduleur_onduleur = :id_onduleur,
            id_installateur_installateur = :id_installateur,
            id_panneau_panneau = :id_panneau,
            code_insee_commune = :code_insee
        WHERE id_installation = :id
    ");

        $ok = $stmt->execute([
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
            ':code_insee' => $data['code_insee'],
            ':id' => $id
        ]);

        if (!$ok) {
            file_put_contents("/tmp/sql_error.log", print_r($stmt->errorInfo(), true));
        }

        return $ok;
    }
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM Installation WHERE id_installation = :id");
        $ok = $stmt->execute([':id' => $id]);

        if (!$ok) {
            file_put_contents("/tmp/sql_error.log", print_r($stmt->errorInfo(), true));
        }

        return $ok;
    }
    public function getStatistiquesAccueil()
    {
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
    public function getAnneesInstallation()
    {
        $sql = "SELECT DISTINCT YEAR(date_installation) AS annee FROM `Installation` ORDER BY annee LIMIT 20";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getDepartementsAleatoires()
    {
        $sql = "
        SELECT DISTINCT LEFT(code_postal, 2) AS departement
        FROM `Commune`
        WHERE code_postal IS NOT NULL
        ORDER BY RAND()
        LIMIT 20
    ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getInstallationsParFiltre($annee = null, $departement = null)
    {
        $sql = "
        SELECT 
            i.id_installation,
            i.latitude,
            i.longitude,
            c.nom_commune AS localite,
            i.puissance
        FROM Installation i
        JOIN Commune c ON i.code_insee_commune = c.code_insee
        WHERE 1=1
    ";

        $params = [];

        if ($annee) {
            $sql .= " AND YEAR(i.date_installation) = :annee";
            $params[":annee"] = $annee;
        }

        if ($departement) {
            $sql .= " AND LEFT(c.code_postal, 2) = :departement";
            $params[":departement"] = $departement;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getOptionsDynamiques($annee = null, $departement = null)
    {
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
    public function getAllPaginated($filtres = [], $page = 1, $parPage = 100)
    {
        $offset = ($page - 1) * $parPage;

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

        $sql .= " ORDER BY i.id_installation ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $installations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "
        SELECT COUNT(*) FROM Installation i
        JOIN Commune c ON i.code_insee_Commune = c.code_insee
        JOIN Département d ON c.code_departement_Département = d.code_departement
        JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
        JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
        JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
        JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
        WHERE 1=1
    ";
        if (!empty($filtres['onduleur'])) $countSql .= " AND mo.nom_marque = :onduleur";
        if (!empty($filtres['panneau'])) $countSql .= " AND mp.nom_marque = :panneau";
        if (!empty($filtres['departement'])) $countSql .= " AND d.code_departement = :departement";

        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        return [
            'installations' => $installations,
            'total' => $total
        ];
    }
    public function recherche($onduleur, $panneau, $departement, $page = 1, $parPage = 10)
    {
        $offset = ($page - 1) * $parPage;

        $sql = "
        SELECT 
            i.id_installation AS id,
            DATE_FORMAT(i.date_installation, '%m/%Y') AS date,
            i.nb_panneaux,
            i.surface,
            i.puissance,
            c.nom_commune AS localisation
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
        if ($onduleur) {
            $sql .= " AND mo.nom_marque = :onduleur";
            $params[':onduleur'] = $onduleur;
        }
        if ($panneau) {
            $sql .= " AND mp.nom_marque = :panneau";
            $params[':panneau'] = $panneau;
        }
        if ($departement) {
            $sql .= " AND d.code_departement = :departement";
            $params[':departement'] = $departement;
        }

        $sql .= " ORDER BY i.date_installation DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "
        SELECT COUNT(*) FROM Installation i
        JOIN Commune c ON i.code_insee_Commune = c.code_insee
        JOIN Département d ON c.code_departement_Département = d.code_departement
        JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
        JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
        JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
        JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
        WHERE 1=1
    ";
        if ($onduleur) $countSql .= " AND mo.nom_marque = :onduleur";
        if ($panneau) $countSql .= " AND mp.nom_marque = :panneau";
        if ($departement) $countSql .= " AND d.code_departement = :departement";

        $countStmt = $this->pdo->prepare($countSql);
        foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        return ['donnees' => $resultats, 'total' => $total];
    }
    public function getMarquesOnduleurs()
    {
        $sql = "SELECT DISTINCT mo.nom_marque 
                FROM MarqueOnduleur mo 
                JOIN Onduleur o ON mo.id_marque = o.id_marque_MarqueOnduleur 
                JOIN Installation i ON o.id_onduleur = i.id_onduleur_Onduleur 
                LIMIT 20";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getMarquesPanneaux()
    {
        $sql = "SELECT DISTINCT mp.nom_marque 
                FROM MarquePanneau mp 
                JOIN Panneau p ON mp.id_marque = p.id_marque_MarquePanneau 
                JOIN Installation i ON p.id_panneau = i.id_panneau_Panneau 
                LIMIT 20";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
    public function getTripletsValides($limite = 20)
    {
        $sql = "
        SELECT DISTINCT 
            mo.nom_marque AS onduleur,
            mp.nom_marque AS panneau,
            d.code_departement AS departement
        FROM Installation i
        JOIN Commune c ON i.code_insee_Commune = c.code_insee
        JOIN Département d ON c.code_departement_Département = d.code_departement
        JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
        JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
        JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
        JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
        ORDER BY RAND()
        LIMIT :limite
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getOptionsCompatibles($onduleur = null, $panneau = null, $departement = null)
    {
        $sql = "
        SELECT DISTINCT 
            mo.nom_marque AS onduleur,
            mp.nom_marque AS panneau,
            d.code_departement AS departement
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

        if ($onduleur) {
            $sql .= " AND mo.nom_marque = :onduleur";
            $params[':onduleur'] = $onduleur;
        }
        if ($panneau) {
            $sql .= " AND mp.nom_marque = :panneau";
            $params[':panneau'] = $panneau;
        }
        if ($departement) {
            $sql .= " AND d.code_departement = :departement";
            $params[':departement'] = $departement;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regrouper les valeurs uniques
        $onduleurs = array_values(array_unique(array_column($rows, "onduleur")));
        $panneaux = array_values(array_unique(array_column($rows, "panneau")));
        $departements = array_values(array_unique(array_column($rows, "departement")));

        if (!$onduleur && !$panneau && !$departement) {
            $departements = $this->getDepartementsAleatoires();
        }

        return [
            "onduleurs" => $onduleurs,
            "panneaux" => $panneaux,
            "departements" => $departements
        ];
    }
    public function verifierConnexionAdmin($identifiant, $mot_de_passe) {
        $stmt = $this->pdo->prepare("SELECT * FROM Admin WHERE identifiant = :identifiant");
        $stmt->execute(['identifiant' => $identifiant]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && $mot_de_passe === $admin['mot_de_passe']) {
            return true;
        }

        return false;
    }
    public function getInstallation($id) {
        $stmt = $this->pdo->prepare("
        SELECT 
            id_installation,
            DATE_FORMAT(date_installation, '%Y-%m-%d') AS date_installation,
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
            code_insee_Commune AS code_insee
        FROM Installation
        WHERE id_installation = :id
    ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getById($id)
    {
        $sql = "
        SELECT i.*, 
               c.nom_commune,
               p.modele_panneau, mp.nom_marque AS marque_panneau,
               o.modele_onduleur, mo.nom_marque AS marque_onduleur,
               inst.nom_installateur
        FROM Installation i
        LEFT JOIN Commune c ON i.code_insee_Commune = c.code_insee
        LEFT JOIN Panneau p ON i.id_panneau_Panneau = p.id_panneau
        LEFT JOIN MarquePanneau mp ON p.id_marque_MarquePanneau = mp.id_marque
        LEFT JOIN Onduleur o ON i.id_onduleur_Onduleur = o.id_onduleur
        LEFT JOIN MarqueOnduleur mo ON o.id_marque_MarqueOnduleur = mo.id_marque
        LEFT JOIN Installateur inst ON i.id_installateur_Installateur = inst.id_installateur
        WHERE i.id_installation = ?
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

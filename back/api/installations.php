<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain'); 


header('Content-Type: application/json');
require_once '../config/database.php'; // ajuste le chemin si besoin

$pdo = getPDO();

// Récupération des filtres
$onduleur = $_GET['onduleur'] ?? null;
$panneau = $_GET['panneau'] ?? null;
$departement = $_GET['departement'] ?? null;

// Requête SQL principale
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

$sql .= " LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);

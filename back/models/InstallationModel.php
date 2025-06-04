<?php
function getStatistiquesAccueil() {
    require_once __DIR__ . '/../config/database.php'; // connexion PDO dans $pdo

    $stats = [];

    $stats['nb_installations'] = $pdo->query("SELECT COUNT(*) FROM Installation")->fetchColumn();
    $stats['nb_par_annee'] = $pdo->query("SELECT COUNT(DISTINCT EXTRACT(YEAR FROM date_installation)) FROM Installation")->fetchColumn();
    $stats['nb_par_region'] = $pdo->query("
        SELECT COUNT(DISTINCT d.id_region_Région)
        FROM Installation i
        JOIN Commune c ON i.code_insee_Commune = c.code_insee
        JOIN Département d ON c.code_departement_Département = d.code_departement
    ")->fetchColumn();
    $stats['nb_par_annee_region'] = $pdo->query("
        SELECT COUNT(*) FROM (
            SELECT DISTINCT EXTRACT(YEAR FROM i.date_installation), d.id_region_Région
            FROM Installation i
            JOIN Commune c ON i.code_insee_Commune = c.code_insee
            JOIN Département d ON c.code_departement_Département = d.code_departement
        ) AS sub
    ")->fetchColumn();
    $stats['nb_installateurs'] = $pdo->query("SELECT COUNT(*) FROM Installateur")->fetchColumn();
    $stats['nb_onduleurs'] = $pdo->query("SELECT COUNT(*) FROM MarqueOnduleur")->fetchColumn();
    $stats['nb_panneaux'] = $pdo->query("SELECT COUNT(*) FROM MarquePanneau")->fetchColumn();

    return $stats;
}

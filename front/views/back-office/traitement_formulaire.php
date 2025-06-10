<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// charge accès base et modèle
require_once __DIR__ . "/../../../back/core/Database.php";
require_once __DIR__ . "/../../../back/models/InstallationModel.php";

$model = new InstallationModel();


// filtre un float avec option max absolue et message d’erreur personnalisé
function filter_float($val, $max = null, &$error = null, $field = '') {
    if (!is_numeric($val)) {
        $error = "Le champ '$field' doit être un nombre décimal.";
        return null;
    }
    $f = floatval($val);
    if ($max !== null && abs($f) > $max) {
        $error = "La valeur de '$field' dépasse la limite autorisée ($max).";
        return null;
    }
    return $f;
}

// filtre un entier avec vérification stricte (évite les '3.0' ou '3abc')
function filter_int($val, $max = null, &$error = null, $field = '') {
    if (!is_numeric($val) || intval($val) != $val) {
        $error = "Le champ '$field' doit être un entier.";
        return null;
    }
    $i = intval($val);
    if ($max !== null && abs($i) > $max) {
        $error = "La valeur de '$field' dépasse la limite autorisée ($max).";
        return null;
    }
    return $i;
}

$errors = []; // stocke les erreurs par champ

// construit le tableau des données avec nettoyage et validation
$data = [
    "date_installation" => $_POST["date_installation"] ?? null,
    "nb_panneaux" => filter_int($_POST["nb_panneaux"] ?? null, 999, $errors['nb_panneaux'], "Nombre de panneaux"),
    "nb_onduleur" => filter_int($_POST["nb_onduleur"] ?? null, 99, $errors['nb_onduleur'], "Nombre d'onduleurs"),
    "surface" => filter_float($_POST["surface"] ?? null, 9999, $errors['surface'], "Surface"),
    "puissance" => filter_float($_POST["puissance"] ?? null, 9999, $errors['puissance'], "Puissance"),
    "latitude" => filter_float($_POST["latitude"] ?? null, 90, $errors['latitude'], "Latitude"),
    "longitude" => filter_float($_POST["longitude"] ?? null, 180, $errors['longitude'], "Longitude"),
    "pente" => filter_float($_POST["pente"] ?? null, 90, $errors['pente'], "Pente"),
    "pente_optimum" => filter_float($_POST["pente_optimum"] ?? null, 90, $errors['pente_optimum'], "Pente optimum"),
    "orientation" => filter_int($_POST["orientation"] ?? null, 360, $errors['orientation'], "Orientation"),
    "orientation_optimum" => filter_int($_POST["orientation_optimum"] ?? null, 360, $errors['orientation_optimum'], "Orientation optimum"),
    "production_pvgis" => filter_float($_POST["production_pvgis"] ?? null, 999999, $errors['production_pvgis'], "Production PVGIS"),
    "id_onduleur_Onduleur" => filter_int($_POST["id_onduleur_Onduleur"] ?? null, 9999, $errors['id_onduleur'], "ID Onduleur"),
    "id_installateur_Installateur" => filter_int($_POST["id_installateur_Installateur"] ?? null, 9999, $errors['id_installateur'], "ID Installateur"),
    "id_panneau_Panneau" => filter_int($_POST["id_panneau_Panneau"] ?? null, 9999, $errors['id_panneau'], "ID Panneau"),
    "code_insee" => $_POST["code_insee"] ?? null
];

// supprime les champs dont l’erreur est vide (filtrage utile pour éviter fausses alertes)
$errors = array_filter($errors, fn($e) => !empty($e));

if (!empty($errors)) {
    // en cas d’erreurs → on affiche un retour HTML simple (à styliser si besoin)
    echo "<ul style='color: red'>";
    foreach ($errors as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
    exit;
}

if ($_POST["action"] === "ajout") {
    // si échec insertion → message d’erreur brut (à améliorer avec redirection ou retour json)
    if (!$model->create($data)) {
        echo "Erreur lors de l'insertion.";
        exit;
    }
} elseif ($_POST["action"] === "modifier") {
    // dans le cas d’une modif → on appelle update avec l’id transmis
    $model->update($_POST["id_installation"], $data);
}

require_once __DIR__ . "/../../../back/controllers/AdminInstallationController.php";

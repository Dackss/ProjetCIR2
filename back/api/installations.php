<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/InstallationModel.php';

$model = new InstallationModel();

// 🔐 Connexion admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET["action"]) && $_GET["action"] === "connexion_admin") {
    $data = json_decode(file_get_contents("php://input"), true);
    $identifiant = $data['identifiant'] ?? '';
    $mot_de_passe = $data['mot_de_passe'] ?? '';

    if ($model->verifierConnexionAdmin($identifiant, $mot_de_passe)) {
        $_SESSION['admin_connecte'] = true;
        $_SESSION['identifiant'] = $identifiant;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
    }
    exit;
}

// 🔍 Gestion des actions GET spécifiques
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "select_options":
            echo json_encode([
                "annees" => $model->getAnneesInstallation(),
                "departements" => $model->getDepartementsAleatoires()
            ]);
            exit;

        case "options_recherche":
            echo json_encode([
                'onduleurs' => $model->getMarquesOnduleurs(),
                'panneaux' => $model->getMarquesPanneaux(),
                'departements' => $model->getDepartementsAleatoires()
            ]);
            exit;

        case "options_dynamiques":
            $annee = $_GET["annee"] ?? null;
            $departement = $_GET["departement"] ?? null;
            echo json_encode($model->getOptionsDynamiques($annee, $departement));
            exit;

        case "filtre":
            $annee = $_GET["annee"] ?? null;
            $departement = $_GET["departement"] ?? null;
            if (!$annee || !$departement) {
                http_response_code(400);
                echo json_encode(["error" => "Paramètres manquants"]);
                exit;
            }
            $installations = $model->getInstallationsParFiltre($annee, $departement);
            $data = array_map(fn($row) => [
                'id_installation' => $row['id_installation'],
                'localite' => $row['localite'],
                'puissance' => $row['puissance'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ], $installations);
            echo json_encode($data);
            exit;

        case "recherche":
            $onduleur = $_GET['onduleur'] ?? null;
            $panneau = $_GET['panneau'] ?? null;
            $departement = $_GET['departement'] ?? null;
            $page = intval($_GET['page'] ?? 1);
            $resultat = $model->recherche($onduleur, $panneau, $departement, $page);
            echo json_encode($resultat);
            exit;

        case "triplets_valides":
            $triplets = $model->getTripletsValides();
            echo json_encode($triplets);
            exit;

        case "filtrage_combine":
            $onduleur = $_GET['onduleur'] ?? null;
            $panneau = $_GET['panneau'] ?? null;
            $departement = $_GET['departement'] ?? null;
            echo json_encode($model->getOptionsCompatibles($onduleur, $panneau, $departement));
            exit;
    }
}

// 🔄 Requêtes REST standard (CRUD)
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $data = $model->getOne($_GET['id']);
        } else {
            $filtres = [
                'onduleur' => $_GET['onduleur'] ?? null,
                'panneau' => $_GET['panneau'] ?? null,
                'departement' => $_GET['departement'] ?? null,
            ];
            $data = $model->getAll($filtres);
        }
        echo json_encode($data);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $success = $model->create($input);
        echo json_encode(['success' => $success]);
        break;

    case 'PUT':
        parse_str(file_get_contents("php://input"), $putData);
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID manquant']);
            break;
        }
        $success = $model->update($_GET['id'], $putData);
        echo json_encode(['success' => $success]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID manquant']);
            break;
        }
        $success = $model->delete($_GET['id']);
        echo json_encode(['success' => $success]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}

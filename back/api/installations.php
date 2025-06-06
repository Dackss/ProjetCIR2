<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/InstallationModel.php';

$model = new InstallationModel();

// Gestion des actions spécifiques (pour la carte)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "select_options":
            echo json_encode([
                "annees" => $model->getAnneesInstallation(),
                "departements" => $model->getDepartementsAleatoires()
            ]);
            exit;

        case "filtre":
            $annee = $_GET["annee"] ?? null;
            $departement = $_GET["departement"] ?? null;

            file_put_contents("debug_filtres.txt", "annee=$annee, departement=$departement\n", FILE_APPEND);

            if (!$annee || !$departement) {
                http_response_code(400);
                echo json_encode(["error" => "Paramètres manquants"]);
                exit;
            }

            $installations = $model->getInstallationsParFiltre($annee, $departement);
            $data = [];

            foreach ($installations as $row) {
                $data[] = [
                    'id_installation' => $row['id_installation'],
                    'localite' => $row['localite'],
                    'puissance' => $row['puissance'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude']
                ];
            }

            echo json_encode($data); // ✅ c'est ici que ça doit s'arrêter
            exit;

        case "options_dynamiques":
            $annee = $_GET["annee"] ?? null;
            $departement = $_GET["departement"] ?? null;

            echo json_encode($model->getOptionsDynamiques($annee, $departement));
            exit;
    }
}

// Requête standard selon la méthode HTTP
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

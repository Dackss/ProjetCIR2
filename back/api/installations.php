<?php
session_start(); // démarre session pour gérer connexion admin

ini_set('display_errors', 1); // active affichage des erreurs pendant dev
ini_set('display_startup_errors', 1); // idem pour erreurs de démarrage
error_reporting(E_ALL); // affiche tous types d’erreurs

require_once __DIR__ . '/../core/Database.php'; // charge gestion base de données
require_once __DIR__ . '/../models/InstallationModel.php'; // charge modèle installations

$model = new InstallationModel(); // instancie modèle pour accéder à la base

// 🔐 connexion admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET["action"]) && $_GET["action"] === "connexion_admin") {
    $data = json_decode(file_get_contents("php://input"), true); // récupère données envoyées en JSON
    $identifiant = $data['identifiant'] ?? ''; // récupère identifiant ou vide
    $mot_de_passe = $data['mot_de_passe'] ?? ''; // récupère mdp ou vide

    if ($model->verifierConnexionAdmin($identifiant, $mot_de_passe)) {
        $_SESSION['admin_connecte'] = true; // stocke connexion en session
        $_SESSION['identifiant'] = $identifiant; // stocke identifiant
        echo json_encode(['success' => true]); // renvoie succès
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']); // renvoie échec
    }
    exit;
}


// 🔍 actions GET spécifiques (utiles pour front JS)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case "select_options": // pour carte leaflet
            echo json_encode([
                "annees" => $model->getAnneesInstallation(), // récupère années présentes
                "departements" => $model->getDepartementsAleatoires() // 20 départements aléatoires
            ]);
            exit;

        case "options_recherche": // pour formulaire de recherche
            echo json_encode([
                'onduleurs' => $model->getMarquesOnduleurs(), // récupère marques onduleurs
                'panneaux' => $model->getMarquesPanneaux(), // récupère marques panneaux
                'departements' => $model->getDepartementsAleatoires() // idem
            ]);
            exit;

        case "options_dynamiques": // pour remplir selects dynamiquement en JS
            $annee = $_GET["annee"] ?? null;
            $departement = $_GET["departement"] ?? null;
            echo json_encode($model->getOptionsDynamiques($annee, $departement));
            exit;

        case "filtre": // pour afficher résultats filtrés sur carte
            $annee = $_GET["annee"] ?? null;
            $departement = $_GET["departement"] ?? null;

            if ($annee === "Tous" || $annee === "") {
                $annee = null;
            }
            if ($departement === "Tous" || $departement === "") {
                $departement = null;
            }

            $installations = $model->getInstallationsParFiltre($annee, $departement); // récupère données filtrées

            $data = array_map(fn($row) => [
                'id_installation' => $row['id_installation'],
                'localite' => $row['localite'],
                'puissance' => $row['puissance'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ], $installations); // simplifie réponse pour carte leaflet

            echo json_encode($data);
            exit;

        case "recherche": // page recherche avec pagination
            $onduleur = $_GET['onduleur'] ?? null;
            $panneau = $_GET['panneau'] ?? null;
            $departement = $_GET['departement'] ?? null;
            $page = intval($_GET['page'] ?? 1);
            $resultat = $model->recherche($onduleur, $panneau, $departement, $page);
            echo json_encode($resultat);
            exit;

        case "triplets_valides": // pour filtrage combiné
            $triplets = $model->getTripletsValides();
            echo json_encode($triplets);
            exit;

        case "filtrage_combine": // pour remplir automatiquement les autres selects selon choix partiel
            $onduleur = $_GET['onduleur'] ?? null;
            $panneau = $_GET['panneau'] ?? null;
            $departement = $_GET['departement'] ?? null;
            echo json_encode($model->getOptionsCompatibles($onduleur, $panneau, $departement));
            exit;
    }
}


// 🔄 gestion des méthodes REST CRUD (create read update delete)
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // lecture
        if (isset($_GET['id'])) {
            $data = $model->getOne($_GET['id']); // une installation
        } else {
            $filtres = [
                'onduleur' => $_GET['onduleur'] ?? null,
                'panneau' => $_GET['panneau'] ?? null,
                'departement' => $_GET['departement'] ?? null,
            ];
            $data = $model->getAll($filtres); // plusieurs installations filtrées
        }
        echo json_encode($data);
        break;

    case 'POST': // création
        $input = json_decode(file_get_contents('php://input'), true);
        $success = $model->create($input); // enregistre nouvelle installation
        echo json_encode(['success' => $success]);
        break;

    case 'PUT': // modification
        parse_str(file_get_contents("php://input"), $putData); // récupère données envoyées
        if (!isset($_GET['id'])) {
            http_response_code(400); // erreur si pas d’id
            echo json_encode(['error' => 'ID manquant']);
            break;
        }
        $success = $model->update($_GET['id'], $putData); // met à jour en base
        echo json_encode(['success' => $success]);
        break;

    case 'DELETE': // suppression
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID manquant']);
            break;
        }
        $success = $model->delete($_GET['id']); // supprime en base
        echo json_encode(['success' => $success]);
        break;

    default: // méthode non reconnue
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}


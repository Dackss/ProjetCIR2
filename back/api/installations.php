<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/InstallationModel.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$model = new InstallationModel();

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
}

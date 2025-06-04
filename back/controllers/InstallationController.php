<?php
require_once __DIR__ . '/../models/InstallationModel.php';
require_once __DIR__ . '/../core/Database.php';

header('Content-Type: application/json');

$model = new InstallationModel();

$filtres = [
	'onduleur' => $_GET['onduleur'] ?? null,
	'panneau' => $_GET['panneau'] ?? null,
	'departement' => $_GET['departement'] ?? null
];

$data = $model->getInstallations($filtres);
echo json_encode($data);
